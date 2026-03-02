<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Comment;
use App\Models\Favorite;
use App\Common\Common;
use App\Http\Requests\CommentRequest;

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
        $isSold = $item->isSold();

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
