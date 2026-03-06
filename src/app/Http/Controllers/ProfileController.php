<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\User;
use App\Common\Common;
use App\Http\Requests\ProfileRequest;

class ProfileController extends Controller
{
    public function index(Request $request){
        $username = Auth::user()->username;
        $avatar = Auth::user()->profile->avatar ?? 'profiles/icon_dummy.png';
        $page = $request->query('page');
        switch ($page) {

        case Common::PAGE_BUY:
            // 購入した商品
            $items = Item::whereHas('orders', function ($query) {
                            $query->where('user_id', Auth::id());
                        })
                        ->notSuspended()
                        ->latest()
                        ->get(['id', 'name', 'img']);
        break;

        default:
            // 出品した商品
            $items = Item::where('user_id', '=', Auth::id())
                        ->notSuspended()
                        ->latest()->get(['id', 'name', 'img']);
            break;
        }
        return view('profile', compact('username','avatar','items'));
    }

    public function edit(){

        //DBから情報を取得
        $username = Auth::user()->username;
        $profile = Auth::user()->profile;
        if($profile){
            // 編集
            // 郵便番号にハイフン追加
            $postcode = $profile->postcode;
            $profile->postcode = substr($postcode, 0, 3) . '-' . substr($postcode, 3);
            $profile->save();
            }

        return view('edit_profile',compact('username','profile'));
    }

    public function store(ProfileRequest $request){

        $data = $request->only(['username', 'postcode', 'address', 'building', 'avatar']);
        // プロフィール画像
        if(isset($data['avatar'])){
            $path = $request->file('avatar')->store('profiles', 'public');
        } else{
            $path = null;
        }

        auth()->user()->profile()->updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'postcode' => preg_replace('/[^0-9]/', '', $data['postcode']),
                'address' => $data['address'],
                'building' => $data['building'] ?: null,
                'avatar' => $path,
            ]
        );

        User::find(Auth::id())->update([
                'username' => $data['username'],
        ]);

        return redirect()->route('profile.index',['page' => \App\Common\Common::PAGE_SELL]);
    }
}
