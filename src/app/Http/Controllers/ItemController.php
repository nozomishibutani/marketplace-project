<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Profile;
use App\Models\Order;
use App\Models\Comment;
use App\Models\Favorite;
use App\Exceptions\SoldOutException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;



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
        // 売り切れかどうか
        $isSold = $this->isSold($item->status);

        // コメント取得
        $content = Comment::where('item_id', $item_id)->latest()->get(['content']);
        $comments['content'] = $content;
        $comments['count']   = $content->count();

        // いいね取得
        $favorite['img'] = 'heart_default.png';
        $favorite['route'] = 'items.favorite';
        if(Auth::check() === true){
            // 自分がいいねしているか
            $isFavorite = Favorite::where('item_id', $item_id)
                            ->where('user_id', Auth::id())
                            ->exists();
            if($isFavorite){
                $favorite['img'] = 'heart_pink.png';
                $favorite['route'] = 'items.unfavorite';
            }
        }
        // いいねの数
        $favorite['count'] = Favorite::where('item_id', $item_id)->count();

        return view('show',compact('item','isSold','comments','favorite'));
    }

    public function confirm($item_id){

        $item = Item::select('id', 'name', 'img', 'price', 'status')->find($item_id);
        // 売り切れかどうか
        $isSold = $this->isSold($item->status);

        // プロフィール登録している住所を取得
        $profileAddress = Profile::select('postcode', 'address', 'building')->find($item_id);
        return view('confirm',compact('item','profileAddress','isSold'));
    }

    public function editAddress($item_id){

        return view('editAddress',compact('item_id'));

    }

    public function updateAddress(Request $request)
    {
        $data = $request->only(['item_id','postcode', 'address', 'building']);

        $newAddress = [
            'postcode' => $data['postcode'],
            'address'  => $data['address'],
            'building' => $data['building'],
        ];

        return redirect()->route('purchase.confirm', ['item_id' => $data['item_id']])->withInput($newAddress);
    }

    public function store(Request $request){

        $data = $request->only(['item_id', 'payment_method', 'postcode', 'address', 'building']);
        if(Auth::check() === null){
            return redirect()->route('/login');
        }

        try {
            DB::transaction(function () use ($data) {

            // 売り切れかどうか
            $item = Item::select('status')->find($data['item_id']);
            $isSold = $this->isSold($item->status);
            if ($isSold) {
                throw new SoldOutException();
            }

            Order::create([
            'user_id' => Auth::id(),
            'item_id' => $data['item_id'],
            'payment_method' => $data['payment_method'],
            'status' => 1, // 要確認
            'postcode' => $data['postcode'],
            'address' => $data['address'],
            'building' => $data['building'],
            ]);
            // 売り切れに変更
            Item::where('id', $data['item_id'])->update(['status' => 2]);
            });

            return redirect()->route('items.index');

        } catch (SoldOutException $e) {

            return redirect()->route('items.show', $data['item_id'])->with('alert', '申し訳ございません。この商品はすでに売り切れました。');

        } catch (\Exception $e) {

            Log::error($e);
            return redirect()->route('items.show', $data['item_id'])->with('alert', 'システムエラーが発生しました。');
        }
}

    public function comment(Request $request){

        // ユーザーid取得
        if(Auth::check() === null){
            return redirect()->route('/login');
        }

        $data = $request->only(['item_id','comment',]);
        Comment::create([
            'user_id' => Auth::id(),
            'item_id' => $data['item_id'],
            'content' => $data['comment'],
        ]);
        return redirect()->route('items.show', ['item_id' => $data['item_id']]);
    }

    public function favorite($item_id){

        // ユーザーid取得
        if (Auth::check() === null) {
            return redirect()->route('/login');
        }

        Favorite::create([
            'user_id' => Auth::id(),
            'item_id' => $item_id,
        ]);
        return redirect()->route('items.show', ['item_id' => $item_id]);

    }

    public function unfavorite($item_id){

        // ユーザーid取得
        if (Auth::check() === null) {
            return redirect()->route('/login');
        }

        Favorite::where('user_id', Auth::id())->where('item_id', $item_id)->delete();

        return redirect()->route('items.show', ['item_id' => $item_id]);

    }

    /**
     * 売り切れかどうか
     */
    public function isSold($status)
    {
        if($status == 1){
            // 出品中
            return false;
        }
        // 売り切れ
        return true;
    }
}
