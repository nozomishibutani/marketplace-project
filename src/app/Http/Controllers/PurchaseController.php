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
use Stripe\PaymentIntent;
use Stripe\Checkout\Session;
use App\Services\OrderService;
use App\Http\Controllers\Controller;

class PurchaseController extends Controller
{
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
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

        $inputData = array_merge($address, ['paymentMethod' => $paymentMethod]);

        return redirect()->route('purchase.confirm', ['item_id' => $item_id])->withInput($inputData);
    }

    public function store(PurchaseRequest $request, $item_id)
    {
        try {
            $item = Item::find($item_id);
            $itemStatuses = $item->itemStatus();
            if ($itemStatuses['sold'] === true) {
                throw new SoldOutException();
            }

            if ($itemStatuses['suspended'] === true) {
                throw new SuspendedException();
            }

            $data = $request->validated();
            $data['payment_method']  = (int)$data['payment_method'];
            $data['item_id'] = $item_id;
            $data['name'] = $item->name;
            $data['price'] = $item->price;

            switch ($data['payment_method']) {
                case Order::PAYMENT_CONVENIENCE:
                    return $this->konbiniPay($data);

                case Order::PAYMENT_CARD:
                    return $this->credit($data);

                default:
                    throw new \Exception();
            }
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

    public function konbiniPay(array $data)
        {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();

            $intent = PaymentIntent::create([
                'amount' => $data['price'],
                'currency' => 'jpy',
                'payment_method_types' => ['konbini'],
                'confirm' => true,
                'payment_method_data' => [
                    'type' => 'konbini',
                    'billing_details' => [
                        'name' => $user->username,
                        'email' => $user->email,
                    ],
                ],
            ]);

            $paymentData = $this->createOrderFromKonbiniPay($intent);
            $this->orderService->createOrder(
                array_merge($data, $paymentData)
            );

            // Stripe 決済画面にリダイレクト
            return redirect($paymentData['voucher_url']);
        }

        private function createOrderFromKonbiniPay($intent): array
        {
            $konbini = $intent->next_action->konbini_display_details;

            return [
                'payment_id' => $intent->id,
                'payment_status' => $intent->status,
                'payment_method' => Order::PAYMENT_CONVENIENCE,
                'payment_expires_at' => date('Y-m-d H:i:s', $konbini->expires_at),
                'voucher_url' => $konbini->hosted_voucher_url,
            ];
        }

    public function credit(array $data)
    {
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $data['name'],
                    ],
                    'unit_amount' => $data['price'],
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
        // Stripeセッション取得 & 注文作成
        // ※Stripe APIやDBで例外が発生する可能性があるため、このメソッド内で捕まえる
        try {
            $session = Session::retrieve($request->session_id);

            $this->createOrderFromCredit($session);

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
                ->with('alert', 'システムエラーが発生しました')
                ->with('alert-type', 'alert-error');
            }
    }

    private function createOrderFromCredit(Session $session)
    {
        return $this->orderService->createOrder([
            'user_id' => $session->metadata->user_id,
            'item_id' => $session->metadata->item_id,
            'postcode' => $session->metadata->postcode,
            'address' => $session->metadata->address,
            'building' => $session->metadata->building,
            'payment_id' => $session->payment_intent,
            'payment_method' => Order::PAYMENT_CARD,
            'payment_status' => $session->payment_status,
            'payment_expires_at' => null,
        ]);
    }

    public function cancel(){
        return redirect()
            ->route('items.index')
            ->with('alert', '決済エラーが発生しました、もう一度やり直してください')
            ->with('alert-type', 'alert-error');
    }
}