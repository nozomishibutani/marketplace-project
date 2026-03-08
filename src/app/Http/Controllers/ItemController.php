<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Comment;
use App\Models\Favorite;
use App\Common\Common;
use App\Http\Requests\CommentRequest;
use App\Models\Category;
use App\Http\Requests\ExhibitionRequest;

class ItemController extends Controller
{
    public function index(Request $request){

        $items = array();
        $tab = $request->query('tab');
        $keyword = $request->query('keyword');
        $user = Auth::user();

        // 未ログイン
        if (!$user) {
            if ($tab === Common::TAB_MYLIST) {
                // マイリストを押下
                $items= array();
                return view('index', compact('items','tab'));
            }
            // 全商品
            $items = Item::notSuspended()
                ->latest()
                ->get(['id', 'name', 'status', 'img']);

            return view('index', compact('items','tab'));
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

            return view('index', compact('items','tab'));
        }

        // 自分が出品した商品以外
        $items = Item::where('user_id', '!=', $user->id)
            ->notSuspended()
            ->latest()
            ->get(['id', 'name', 'status', 'img']);

        return view('index', compact('items','tab'));
    }


    public function show($item_id){

        $item = Item::with(['categories', 'favorites', 'comments.user.profile'])->findOrFail($item_id);
        $itemStatuses = $item->itemStatus();
        // 値段にコンマ追加
        $item->price = number_format($item->price);
        // コメント数
        $commentsCount = $item->comments->count();
        // いいね取得
        $isFavorite = false;
        if(Auth::check() === true){
            // 自分がいいねしているか
            $isFavorite = Favorite::where('item_id', $item_id)
                            ->where('user_id', Auth::id())
                            ->exists();
        }
        // いいね数
        $favoritesCount = $item->favorites->count();
        // プロフィール登録がなければダミー画像
        $avatar = array();
        foreach ($item->comments as $comment) {
            $avatar[$comment->id] = $comment->user->profile->avatar ?? 'profiles/icon_dummy.png';
        }

        return view('show',compact('item','itemStatuses','isFavorite','favoritesCount','commentsCount','avatar'));
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

    public function create(){

        // 商品カテゴリーを取得
        $categories = Category::pluck('name', 'id');
        return view('sell',compact('categories'));
    }

    public function store(ExhibitionRequest $request){

        $data = $request->only(['name', 'brand_name', 'description', 'price', 'condition', 'img', 'categories']);

        // 画像保存
        $path = $request->file('img')->store('items', 'public');
        $item = Item::create([
                    'user_id' => Auth::id(),
                    'name' => $data['name'],
                    'brand_name' => $data['brand_name'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'condition' => $data['condition'],
                    'img' => $path,
                    ]);
        // 選択カテゴリーをcategory_itemテーブルに登録
        $item->categories()->attach($data['categories']);

        return redirect()->route('profile.index', ['page' => \App\Common\Common::PAGE_SELL]);

    }

    public function search(Request $request){
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
