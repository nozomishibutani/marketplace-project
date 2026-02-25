<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Common\Common;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->query('keyword');
        $tab = $request->input('tab');

        if ($tab === Common::TAB_MYLIST) {
            // マイリストの中で検索
            if($keyword == "" || $keyword == null){
                // 全表示
                $items = Item::whereHas('favorites', function ($query) {
                                $query->where('user_id', Auth::id());
                            })
                            ->notSuspended()
                            ->latest()
                            ->get(['id', 'name', 'status', 'img']);
            } else {
                $items = Item::whereHas('favorites', function ($query) {
                                $query->where('user_id', Auth::id());
                            })
                            ->where('name', 'LIKE', "%{$keyword}%")
                            ->notSuspended()
                            ->latest()
                            ->get(['id', 'name', 'status', 'img']);

                session()->flash('keyword', $keyword);
            }
        return view('index',compact('items'));
        }
        // 検索ワードなし
        if($keyword == "" || $keyword == null){
            $items = Item::notSuspended()->latest()->get(['id', 'name', 'status', 'img']);
        } else {
            $items = Item::where('name', 'LIKE', "%{$keyword}%")->notSuspended()->latest()->get(['id', 'name', 'status', 'img']);
            session()->flash('keyword', $keyword);
        }
        return view('index',compact('items', 'tab'));
    }
}

