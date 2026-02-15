<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Profile;
use App\Models\Order;

class ItemController extends Controller
{
    public function index(){

        // セッション削除（暫定対応）
        session()->forget('address_edit');
        // 出品されている全商品（自分が出品したもの以外）を一覧表示する
        $items = Item::select('id', 'name', 'img')
                ->latest()
                ->get();

        // ログイン済みならuser_id取得する

        return view('index',compact('items'));
        // マイリスト（いいねした商品だけ表示される）

        // 商品検索
    }

    public function show($item_id){

        // セッション削除（暫定対応）
        session()->forget('address_edit');

        $item = Item::with('category')->find($item_id);
        // 売り切れた場合
        $soldFlg = false;
        if($item->status != 1){
            // sold表示にして購入できないようにする
            // 購入ボタン非活性にする
            $soldFlg = true;
        }
        return view('show',compact('item','soldFlg'));
    }

    public function confirm($item_id){

        $item = Item::select('id', 'name', 'img', 'price', 'status')->find($item_id);
        // 売り切れた場合
        $soldFlg = false;
        if($item->status != 1){
            // sold表示にして購入できないようにする
            // 購入ボタン非活性にする
            $soldFlg = true;
        }
        // プロフィール登録している住所を取得
        $shippingAddress = Profile::select('postcode', 'address', 'building')->find($item_id);
        return view('confirm',compact('item','shippingAddress','soldFlg'));
    }

    public function editAddress($item_id){

        return view('editAddress',compact('item_id'));

    }

    public function updateAddress(Request $request)
    {
        session([
            'address_edit' => [
                'postcode' => $request->postcode,
                'address' => $request->address,
                'building' => $request->building,
            ]
        ]);

        return redirect()->route('purchase.confirm', ['item_id' => $request->itemId]);
    }

    public function store(Request $request){
        $data = $request->only(['user_id', 'item_id', 'payment_method', 'status', 'postcode', 'address', 'building']);

        // 注文作成など
        Order::create([
            //'user_id' => auth()->id(),
            'user_id' => $data['user_id'],
            'item_id' => $data['item_id'],
            'payment_method' => $data['payment_method'],
            'status' => $data['status'],
            'postcode' => $data['postcode'],
            'address' => $data['address'],
            'building' => $data['building'],
        ]);
        Item::updated([
            'status' => 2,
        ]);

        return redirect()->route('items.index');
    }
}
