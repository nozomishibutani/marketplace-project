<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Profile;
use App\Models\Order;
use App\Exceptions\SoldOutException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\PurchaseRequest;
use Stripe\Stripe;
use Stripe\Checkout\Session;



class PurchaseController extends Controller
{
    public function confirm(Request $request, $item_id)
    {
        $item = Item::select('id', 'name', 'img', 'price', 'status')->find($item_id);
        // 売り切れかどうか
        $isSale = $item->isSale();
        // お支払方法
        $paymentMethod = $request->input('payment_method');

        if (!$paymentMethod) {
            // 最初のアクセス
            $address = Profile::where('user_id', Auth::id())
                ->select('postcode', 'address', 'building')
                ->first();
            // 郵便番号にハイフン追加
            $postcode = $address['postcode'];
            $address['postcode'] = substr($postcode, 0, 3) . '-' . substr($postcode, 3);
        } else {
            // フォームに入力のある住所
            $address = $request->only(['postcode', 'address', 'building']);
        }
        return view('confirm', compact('item', 'address', 'isSale', 'paymentMethod'));
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
        $isSale = $item->isSale();
        // 支払い方法
        $paymentMethod = $request->input('payment_method');

        $address = $request->only(['postcode', 'address', 'building']);

        return view('confirm', compact('item', 'address', 'isSale', 'paymentMethod'));
    }

    public function store(PurchaseRequest $request, $item_id)
    {
        $data = $request->only(['payment_method', 'postcode', 'address', 'building']);
        $data['item_id'] = $item_id;

        // コンビニ払い
        if ($data['payment_method'] == Order::PAYMENT_CONVENIENCE) {
            $this->createOrder($data);

            return redirect()->route('items.index');
        }
        // カード払い
        return $this->redirectToStripe($data);

    }

    public function createOrder($data)
    {

        try {
            // 売り切れかどうか
            $item = Item::select('status')->find($data['item_id']);
            $isSale = $item->isSale();
            if (!$isSale) {
                throw new SoldOutException();
            }

            DB::transaction(function () use ($data) {

                Order::create([
                'user_id' => Auth::id(),
                'item_id' => $data['item_id'],
                'payment_method' => $data['payment_method'],
                'status' => 1, // 要確認
                'postcode' => preg_replace('/[^0-9]/', '', $data['postcode']),
                'address' => $data['address'],
                'building' => $data['building'] ?: null,
                ]);
                // 売り切れに変更
                Item::where('id', $data['item_id'])->update(['status' => item::STATUS_SOLD]);
            });


        } catch (\Exception $e) {

            Log::error($e);
            return redirect()->route('items.show', $data['item_id'])->with('alert', 'システムエラーが発生しました。');
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
                'cancel_url' => route('checkout.cancel'),
            ]);
            // Stripe 決済画面にリダイレクト
            return redirect($session->url);
        }

        public function success(Request $request)
        {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            $session = \Stripe\Checkout\Session::retrieve(
                $request->session_id
            );

            // 念のため支払い確認（超重要）
            // if ($session->payment_status !== 'paid') {
            //     abort(403);
            // }

            // ここで createOrder に渡す
            $this->createOrderFromStripe($session);

            return redirect()->route('items.index');

        }

        private function createOrderFromStripe($session)
        {
            return $this->createOrder([
                'user_id' => $session->metadata->user_id,
                'item_id' => $session->metadata->item_id,
                'postcode' => $session->metadata->postcode,
                'address' => $session->metadata->address,
                'building' => $session->metadata->building,
                'payment_method' => Order::PAYMENT_CARD,
            ]);
        }
}