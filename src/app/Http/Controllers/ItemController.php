<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Profile;
use App\Models\Order;
use App\Models\Comment;
use App\Models\Favorite;

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
        $isSold = false;
        if($item->status != 1){
            // sold表示にして購入できないようにする
            // 購入ボタン非活性にする
            $isSold = true;
        }
        // コメント取得
        $content = Comment::where('item_id', $item_id)
                        ->latest()
                        ->get(['content']);

        $comments['content'] = $content;
        $comments['count']   = $content->count();

        // いいね取得
        // 自分がいいねしているか
        $isFavorite = false;
        $isFavorite = Favorite::where('item_id', $item_id)
                        ->where('user_id', 1)
                        ->exists();
        if($isFavorite){
            $favorite['img'] = 'heart_pink.png';
            $favorite['route'] = 'items.unfavorite';
        }else{
            $favorite['img'] = 'heart_default.png';
            $favorite['route'] = 'items.favorite';
        }
        // いいねの数
        $favorite['count'] = Favorite::where('item_id', $item_id)->count();


        return view('show',compact('item','isSold','comments','favorite'));
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
        $data = $request->only(['item_id','postcode', 'address', 'building']);
        // 変更住所をセッションに登録する
        session([
            'address_edit' => [
                'postcode' => $data['postcode'],
                'address' => $data['address'],
                'building' => $data['building'],
            ]
        ]);

        return redirect()->route('purchase.confirm', ['item_id' => $data['item_id']]);
    }

    public function store(Request $request){

        $data = $request->only(['user_id', 'item_id', 'payment_method', 'status', 'postcode', 'address', 'building']);

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

    public function comment(Request $request){

        $data = $request->only(['user_id','item_id','comment',]);
        Comment::create([
            //'user_id' => auth()->id(),
            'user_id' => $data['user_id'],
            'item_id' => $data['item_id'],
            'content' => $data['comment'],
        ]);
        return redirect()->route('items.show', ['item_id' => $data['item_id']]);
    }

    public function favorite($item_id){

        Favorite::create([
            //'user_id' => auth()->id(),
            'user_id' => 1,
            'item_id' => $item_id,
        ]);
        return redirect()->route('items.show', ['item_id' => $item_id]);

    }

    public function unfavorite($item_id){

        Favorite::where('user_id', 1)->where('item_id', $item_id)->delete();

        return redirect()->route('items.show', ['item_id' => $item_id]);

    }
}
