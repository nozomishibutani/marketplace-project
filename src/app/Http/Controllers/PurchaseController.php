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


class PurchaseController extends Controller
{
    public function confirm(Request $request, $item_id)
    {
        $item = Item::select('id', 'name', 'img', 'price', 'status')->find($item_id);
        // 売り切れかどうか
        $isSold = $item->isSold();
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
        return view('confirm', compact('item', 'address', 'isSold', 'paymentMethod'));
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
        $isSold = $item->isSold();
        // 支払い方法
        $paymentMethod = $request->input('payment_method');

        $address = $request->only(['postcode', 'address', 'building']);

        return view('confirm', compact('item', 'address', 'isSold', 'paymentMethod'));
    }

    public function store(PurchaseRequest $request, $item_id)
    {

        $data = $request->only(['payment_method', 'postcode', 'address', 'building']);

        try {
            DB::transaction(function () use ($data, $item_id) {

                // 売り切れかどうか
                $item = Item::select('status')->find($item_id);
                $isSold = $item->isSold();
                if ($isSold) {
                    throw new SoldOutException();
                }

                Order::create([
                'user_id' => Auth::id(),
                'item_id' => $item_id,
                'payment_method' => $data['payment_method'],
                'status' => 1, // 要確認
                'postcode' => preg_replace('/[^0-9]/', '', $data['postcode']),
                'address' => $data['address'],
                'building' => $data['building'] ?: null,
                ]);
                // 売り切れに変更
                Item::where('id', $item_id)->update(['status' => item::STATUS_SOLD]);
            });

            return redirect()->route('items.index');

        } catch (SoldOutException $e) {

            return redirect()->route('items.show', $item_id)->with('alert', '申し訳ございません。この商品はすでに売り切れました。');

        } catch (\Exception $e) {

            Log::error($e);
            return redirect()->route('items.show', $item_id)->with('alert', 'システムエラーが発生しました。');
        }
    }
}
