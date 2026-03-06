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



class PurchaseController extends Controller
{
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function confirm(Request $request, $item_id)
    {
        $item = Item::select('id', 'name', 'img', 'price', 'status')->find($item_id);
        // 売り切れかどうか
        $itemStatuses = $item->itemStatus();
        // お支払方法
        $paymentMethod = $request->input('payment_method');

        if (!$paymentMethod) {
            // 最初のアクセス
            $address = Profile::where('user_id', Auth::id())
                ->select('postcode', 'address', 'building')
                ->first();
                if($address){
                    // プロフィール登録済み
                    $postcode = $address['postcode'];
                    $address['postcode'] = substr($postcode, 0, 3) . '-' . substr($postcode, 3);
                }else{
                    $address['postcode'] = null;
                    $address['address'] = null;
                    $address['building'] = null;
                }
        } else {
            // フォームに入力のある住所
            $address = $request->only(['postcode', 'address', 'building']);
        }
        return view('confirm', compact('item', 'address', 'itemStatuses', 'paymentMethod'));
    }

    public function editAddress(Request $request, $item_id)
    {

        // 支払い方法
        $paymentMethod = $request->input('payment_method');

        return view('editAddress', compact('item_id', 'paymentMethod'));

    }

    public function updateAddress(AddressRequest $request, $item_id)
    {
        $item = Item::select('id', 'name', 'img', 'price', 'status')->find($item_id);
        // 売り切れかどうか
        $itemStatuses = $item->itemStatus();
        // 支払い方法
        $paymentMethod = $request->input('payment_method');

        $address = $request->only(['postcode', 'address', 'building']);

        return view('confirm', compact('item', 'address', 'itemStatuses', 'paymentMethod'));
    }

        public function store(PurchaseRequest $request, $item_id)
        {
            $data = $request->only(['payment_method', 'postcode', 'address', 'building']);
            $data['item_id'] = $item_id;

            try {
                // コンビニ払い
                if ($data['payment_method'] == Order::PAYMENT_CONVENIENCE) {
                    $data['status'] = Order::STATUS_PENDING;
                    $this->orderService->createOrder($data);
                    return redirect()->route('items.index')->with('alert', 'ご購入ありがとうございました。');
                }
                // カード払い
                return $this->redirectToStripe($data);

            } catch (SoldOutException) {

                $item = Item::find($data['item_id']);
                return redirect()
                    ->route('items.show', $item_id)
                    ->with('alert', $item->name . 'は売り切れました');

            } catch (SuspendedException) {

            $item = Item::find($data['item_id']);
            return redirect()
                ->route('items.show', $item_id)
                ->with('alert', '現在、' .$item->name . 'は出品を中止しております');

        } catch (\Exception $e) {

                Log::error($e);
                return redirect()
                    ->route('items.show', $item_id)
                    ->with('alert', 'システムエラーが発生しました');
            }
        }

    public function redirectToStripe(array $data)
        {
            $item = Item::select('name', 'price', 'status')->find($data['item_id']);
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

            return redirect()->route('items.index')->with('alert', 'ご購入ありがとうございました。');

        } catch (SoldOutException) {

            $item = Item::find($session->metadata->item_id);
            return redirect()
                ->route('items.show', $session->metadata->item_id)
                ->with('alert', $item->name . 'は売り切れました');

        } catch (SuspendedException) {

            $item = Item::find($session->metadata->item_id);
            return redirect()
                ->route('items.show', $session->metadata->item_id)
                ->with('alert', '現在、' .$item->name . 'は出品を中止しております');

        } catch (\Exception $e) {

            Log::error($e);
            return redirect()
                ->route('items.show', $session->metadata->item_id)
                ->with('alert', 'エラーが発生しました');
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
                'payment_method' => Order::PAYMENT_CARD,
                'status' => Order::STATUS_PAID,
            ]);
        }

        public function cancel(){
            return redirect()
                ->route('items.index')
                ->with('alert', '決済エラーが発生しました、もう一度やり直してください。');
        }
}