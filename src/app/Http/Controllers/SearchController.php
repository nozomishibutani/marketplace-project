<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        // 全ヘッダー内の検索機能
        $keyword = $request->query('keyword');
        // 検索ワードなし
        if($keyword == "" || $keyword == null){
            $items = Item::notSuspended()->latest()->get(['id', 'name', 'status', 'img']);
        } else {
            $items = Item::where('name', 'LIKE', "%{$keyword}%")->notSuspended()->latest()->get(['id', 'name', 'status', 'img']);
            session()->flash('keyword', $keyword);
        }
        return view('index',compact('items'));
    }
}

