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
        $keyword = $request->query('keyword');
        $user = Auth::user();

        // 未ログイン
        if (!$user) {
            // 全商品
            $items = Item::notSuspended()
                ->latest()
                ->get(['id', 'name', 'status', 'img']);

            return view('index', compact('items'));
        }

        // ログイン済み
        // 検索状態をマイリストでも保持
        if (str_contains(url()->previous(), 'search')) {
            // search経由
            return $this->search($request);
        }

        if ($tab === Common::TAB_MYLIST) {
            $items = Item::whereHas('favorites', function ($query) use ($user) {
                $query->where('user_id', $user->id);
                })
                ->notSuspended()
                ->latest()
                ->get(['id', 'name', 'status', 'img']);

            return view('index', compact('items'));
        }

        // 自分が出品した商品以外
        $items = Item::where('user_id', '!=', $user->id)
            ->notSuspended()
            ->latest()
            ->get(['id', 'name', 'status', 'img']);

        return view('index', compact('items'));
    }


    public function show($item_id){

        $item = Item::with('categories')->findOrFail($item_id);
        // 売り切れかどうか
        $isSold = $this->isSold($item->status);

        // コメント取得
        $data = Comment::where('item_id', $item_id)->latest()->get(['content']);
        $content['content'] = $data;
        $content['count']   = $data->count();

        // いいね取得
        $isFavorite = null;
        if(Auth::check() === true){
            // 自分がいいねしているか
            $isFavorite = Favorite::where('item_id', $item_id)
                            ->where('user_id', Auth::id())
                            ->exists();
        }
        // いいねの数
        $favoriteCount = Favorite::where('item_id', $item_id)->count();

        return view('show',compact('item','isSold','content','isFavorite','favoriteCount'));
    }

    public function confirm(Request $request, $item_id){

        $item = Item::select('id', 'name', 'img', 'price', 'status')->find($item_id);
        // 売り切れかどうか
        $isSold = $this->isSold($item->status);
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
        return view('confirm',compact('item','address','isSold' ,'paymentMethod'));
    }

    public function editAddress(Request $request ,$item_id){

        // 支払い方法
        $paymentMethod = $request->input('payment_method');

        return view('editAddress',compact('item_id','paymentMethod'));

    }

    public function updateAddress(AddressRequest $request, $item_id)
    {
        $item = Item::select('id', 'name', 'img', 'price', 'status')->find($item_id);
        // 売り切れかどうか
        $isSold = $this->isSold($item->status);
        // 支払い方法
        $paymentMethod = $request->input('payment_method');

        $address = $request->only(['postcode', 'address', 'building']);

        return view('confirm',compact('item','address','isSold' ,'paymentMethod'));
    }

    public function store(PurchaseRequest $request, $item_id){

        $data = $request->only(['payment_method', 'postcode', 'address', 'building']);

        try {
            DB::transaction(function () use ($data, $item_id) {

            // 売り切れかどうか
            $item = Item::select('status')->find($item_id);
            $isSold = $this->isSold($item->status);
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

    public function comment(CommentRequest $request, $item_id){

        $data = $request->only(['content']);
        Comment::create([
            'user_id' => Auth::id(),
            'item_id' => $item_id,
            'content' => $data['content'],
        ]);
        return redirect()->route('items.show', ['item_id' => $item_id]);
    }

    public function favorite($item_id){

        Favorite::create([
            'user_id' => Auth::id(),
            'item_id' => $item_id,
        ]);
        return redirect()->route('items.show', ['item_id' => $item_id]);

    }

    public function unfavorite($item_id){

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

    /**
     * 商品検索
     */
    public function search(Request $request)
    {
        $keyword = $request->query('keyword');
        $tab = $request->input('tab');

        $query = Item::query()->notSuspended();

        // マイリスト
        if ($tab === Common::TAB_MYLIST) {
            $query->whereHas('favorites', function ($q) {
                $q->where('user_id', Auth::id());
            });
        }

        // キーワード
        if (!empty($keyword)) {
            $query->where('name', 'LIKE', "%{$keyword}%");
        }

        $items = $query->latest()->get(['id', 'name', 'status', 'img']);

        return view('index',compact('items', 'tab'));
    }
}
