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
use App\Common\Common;
use App\Http\Requests\CommentRequest;
use App\Http\Requests\AddressRequest;

use App\Http\Requests\PurchaseRequest;





class ItemController extends Controller
{
    public function index(Request $request){

        $items = array();
        $tab = $request->query('tab');

        if(Auth::id() !== null){
            // ログイン済み
            if ($tab === Common::TAB_MYLIST) {
                // マイリスト（いいねした商品だけ表示される）
                $items = Item::whereHas('favorites', function ($query) {
                            $query->where('user_id', Auth::id());
                        })
                        ->notSuspended()
                        ->latest()
                        ->get(['id', 'name', 'status', 'img']);
            } else {
                // 自分が出品した商品以外
                $items = Item::where('user_id', '!=', Auth::id())
                        ->notSuspended()
                        ->latest()->get(['id', 'name', 'status', 'img']);
                }
        }else{
            if ($tab !== Common::TAB_MYLIST) {
                $items = Item::notSuspended()->latest()->get(['id', 'name', 'status', 'img']);
            }
        }
        return view('index',compact('items'));
    }

    public function show($item_id){

        $item = Item::with('category')->find($item_id);
        // 売り切れかどうか
        $isSold = $this->isSold($item->status);

        // コメント取得
        $data = Comment::where('item_id', $item_id)->latest()->get(['content']);
        $content['content'] = $data;
        $content['count']   = $data->count();

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

        return view('show',compact('item','isSold','content','favorite'));
    }

    public function confirm($item_id){

        $item = Item::select('id', 'name', 'img', 'price', 'status')->find($item_id);
        // 売り切れかどうか
        $isSold = $this->isSold($item->status);

        // プロフィール登録している住所を取得
        $profileAddress = Profile::select('postcode', 'address', 'building')->find($item_id);
        // 郵便番号にハイフン追加
        $postcode = $profileAddress['postcode'];
        if (strlen($postcode) === 7) {
            $profileAddress['postcode'] = substr($postcode, 0, 3) . '-' . substr($postcode, 3);
        }

        return view('confirm',compact('item','profileAddress','isSold'));
    }

    public function editAddress($item_id){

        return view('editAddress',compact('item_id'));

    }

    public function updateAddress(AddressRequest $request)
    {
        $data = $request->only(['item_id','postcode', 'address', 'building']);

        $newAddress = [
            'postcode' => $data['postcode'],
            'address'  => $data['address'],
            'building' => $data['building'] ?? '',
        ];
        return redirect()->route('purchase.confirm', ['item_id' => $data['item_id']])->withInput($newAddress);
    }

    public function store(PurchaseRequest $request){

        $data = $request->only(['item_id', 'payment_method', 'postcode', 'address', 'building']);
        if(Auth::check() === null){
            return redirect('/login');
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
            'postcode' => preg_replace('/[^0-9]/', '', $data['postcode']),
            'address' => $data['address'],
            'building' => $data['building'] ?: null,
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

    public function comment(CommentRequest $request){

        // ユーザーid取得
        if(Auth::check() === null){
            return redirect('/login');
        }

        $data = $request->only(['item_id','content',]);
        Comment::create([
            'user_id' => Auth::id(),
            'item_id' => $data['item_id'],
            'content' => $data['content'],
        ]);
        return redirect()->route('items.show', ['item_id' => $data['item_id']]);
    }

    public function favorite($item_id){

        // ユーザーid取得
        if (Auth::check() === null) {
            return redirect('/login');
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
            return redirect('/login');
        }

        Favorite::where('user_id', Auth::id())->where('item_id', $item_id)->delete();

        return redirect()->route('items.show', ['item_id' => $item_id]);

    }

    /**
     * 売り切れかどうか
     */
    public function isSold($status)
    {
        if($status == Item::STATUS_ON_SALE){
            // 出品中
            return false;
        }
        // 売り切れ
        return true;
    }
}
