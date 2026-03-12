<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Profile;
use App\Models\Order;
use App\Exceptions\SoldOutException;
use App\Exceptions\SuspendedException;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\PurchaseRequest;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Services\OrderService;
use App\Http\Controllers\Controller;

class PurchaseController extends Controller
{
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function confirm(Request $request, $item_id)
    {
        $item = Item::select('id', 'user_id', 'name', 'img', 'price', 'status')->find($item_id);
        if (!$item) {
            return $this->redirectItemNotAvailable();
        }
        if ($item->user_id === Auth::id()) {
            return redirect()->route('items.show', $item_id)
                ->with('alert', '自分の商品は購入できません')
                ->with('alert-type', 'alert-error');
        }
        // 売り切れかどうか
        $itemStatuses = $item->itemStatus();
        // お支払方法
        $paymentMethod = $request->input('payment_method');
        // 値段にコンマ追加
        $item->price = number_format($item->price);

        // 配送先変更からのリダイレクト
        if (session()->hasOldInput()) {
            $address['postcode'] = $request->old('postcode');
            $address['address']  = $request->old('address');
            $address['building'] = $request->old('building');
            $paymentMethod = $request->old('paymentMethod');

            return view('confirm', compact('item', 'address', 'itemStatuses', 'paymentMethod'));
        }

        // 最初の遷移
        if (!$paymentMethod) {
            $address = Profile::where('user_id', Auth::id())
                ->select('postcode', 'address', 'building')
                ->first();

                if($address){
                    // プロフィール登録あり
                    $address['postcode'] = substr($address['postcode'], 0, 3) . '-' . substr($address['postcode'], 3);
                    $address['address'] = $address['address'];
                    $address['building'] = $address['building'] ?? null;
                }else{
                    $address['postcode'] = null;
                    $address['address'] = null;
                    $address['building'] = null;
                }

                return view('confirm', compact('item', 'address', 'itemStatuses', 'paymentMethod'));
        }

        // 支払い方法変更時の遷移
        // フォーム内容を表示
        $address = $request->only(['postcode', 'address', 'building']);

        return view('confirm', compact('item', 'address', 'itemStatuses', 'paymentMethod'));
    }

    public function editAddress(Request $request, $item_id)
    {
        // 支払い方法
        $paymentMethod = $request->input('payment_method');

        return view('edit_address', compact('item_id', 'paymentMethod'));
    }

    public function updateAddress(AddressRequest $request, $item_id)
    {
        // 支払い方法
        $paymentMethod = $request->input('payment_method');
        $address = $request->only(['postcode', 'address', 'building']);

        // 配列にまとめる
        $inputData = array_merge($address, ['paymentMethod' => $paymentMethod]);

        return redirect()->route('purchase.confirm', ['item_id' => $item_id])->withInput($inputData);
    }

    public function store(PurchaseRequest $request, $item_id)
    {
        $data = $request->only(['payment_method', 'postcode', 'address', 'building']);
        $data['item_id'] = $item_id;

        try {
            // コンビニ払い
            // TODO: 決済機能実装までテスト用のpayment_idを付与
            $data['payment_id'] = now()->format('YmdHis');
            if ($data['payment_method'] == Order::PAYMENT_CONVENIENCE) {
                $data['status'] = Order::STATUS_PENDING;
                $this->orderService->createOrder($data);
                return redirect()->route('items.index')
                    ->with('alert', 'ご購入ありがとうございました。')
                    ->with('alert-type', 'alert-success');
            }
            // カード払い
            return $this->redirectToStripe($data);

        } catch (SoldOutException) {
            return redirect()
                ->route('items.show', $item_id)
                ->with('alert', 'この商品は売り切れました')
                ->with('alert-type', 'alert-error');
        } catch (SuspendedException) {
            return redirect()
                ->route('items.show', $item_id)
                ->with('alert', 'この商品は削除されたか、現在購入できません')
                ->with('alert-type', 'alert-error');
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()
                ->route('items.index')
                ->with('alert', 'システムエラーが発生しました')
                ->with('alert-type', 'alert-error');
        }
    }

    public function redirectToStripe(array $data)
        {
            $item = Item::find($data['item_id']);
            $itemStatuses = $item->itemStatus();

            if ($itemStatuses['sold'] == true) {
                throw new SoldOutException();
            }

            if ($itemStatuses['suspended'] == true) {
                throw new SuspendedException();
            }

            // Stripe秘密キーセット
            Stripe::setApiKey(env('STRIPE_SECRET'));
            // Checkout Session 作成
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'jpy',
                        'product_data' => [
                            'name' => $item->name,
                        ],
                        'unit_amount' => $item->price,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',

                'metadata' => [
                                'item_id' => $data['item_id'],
                                'user_id' => Auth::id(),
                                'postcode' => $data['postcode'],
                                'address' => $data['address'],
                                'building' => $data['building'],
                            ],

                'success_url' => route('purchase.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('purchase.cancel'),
            ]);
            // Stripe 決済画面にリダイレクト
            return redirect($session->url);
        }

    public function success(Request $request)
    {
    try {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = Session::retrieve($request->session_id);

        $this->createOrderFromStripe($session);

        return redirect()->route('items.index')
                ->with('alert', 'ご購入ありがとうございました。')
                ->with('alert-type', 'alert-success');

    } catch (SoldOutException) {
        /** @var \Stripe\Checkout\Session $session */
        return redirect()
            ->route('items.show', $session->metadata->item_id)
            ->with('alert', 'この商品は売り切れのため、現在購入できません。<br>返金処理しました')
            ->with('alert-type', 'alert-error');
    } catch (SuspendedException) {
        /** @var \Stripe\Checkout\Session $session */
        return redirect()
            ->route('items.show', $session->metadata->item_id)
            ->with('alert', 'この商品は削除されたか、現在購入できません。<br>返金処理しました')
            ->with('alert-type', 'alert-error');
    } catch (\Exception $e) {
        Log::error($e);
        return redirect()
            ->route('items.index')
            ->with('alert', 'エラーが発生しました')
            ->with('alert-type', 'alert-error');
        }
    }

    private function createOrderFromStripe($session)
    {
        return $this->orderService->createOrder([
            'user_id' => $session->metadata->user_id,
            'item_id' => $session->metadata->item_id,
            'postcode' => $session->metadata->postcode,
            'address' => $session->metadata->address,
            'building' => $session->metadata->building,
            'payment_id' => $session->payment_intent,
            'payment_method' => Order::PAYMENT_CARD,
            'status' => Order::STATUS_PAID,
        ]);
    }

    public function cancel(){
        return redirect()
            ->route('items.index')
            ->with('alert', '決済エラーが発生しました、もう一度やり直してください')
            ->with('alert-type', 'alert-error');
    }
}