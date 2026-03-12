<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\User;
use App\Models\Profile;
use App\Common\Common;
use App\Http\Requests\ProfileRequest;

class ProfileController extends Controller
{
    public function index(Request $request) {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $username = $user->username;
        $avatar = $user->profile->avatar ?? Profile::DEFAULT_AVATAR;
        $page = $request->query('page');
        $items = array();

        switch ($page) {
        case Common::PAGE_BUY:
            // 購入した商品
            $items = Item::whereHas('orders', function ($query) {
                            $query->where('user_id', Auth::id());
                        })
                        ->notSuspended()
                        ->latest()
                        ->get(['id', 'name', 'img']);
            // 購入商品にはsold表示しない
            $items->status = null;
        break;

        case Common::PAGE_SELL:
            // 出品した商品
            $items = Item::where('user_id', '=', Auth::id())
                        ->notSuspended()
                        ->latest()->get(['id', 'name', 'img', 'status']);
            break;
        }
        return view('profile', compact('username','avatar','items'));
    }

    public function edit() {
        /** @var \App\Models\User|null $user */
        $user= Auth::user();
        $username = $user->username;
        $profile = $user->profile;
        if ($profile) {
            // 郵便番号にハイフン追加
            $postcode = $profile->postcode;
            $profile->postcode = substr($postcode, 0, 3) . '-' . substr($postcode, 3);
            }

        return view('edit_profile',compact('username','profile'));
    }

    public function store(ProfileRequest $request) {
        $data = $request->only(['username', 'postcode', 'address', 'building', 'avatar']);
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        // プロフィール画像
        $path = null;
        if (isset($data['avatar'])) {
            // 画像変更
            $path = $request->file('avatar')->store('profiles', 'public');
        } elseif ($user->profile->avatar) {
            // 既に登録があり画像変更しない
            $path = $user->profile->avatar;
        }

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'postcode' => preg_replace('/[^0-9]/', '', $data['postcode']),
                'address' => $data['address'],
                'building' => $data['building'] ?? null,
                'avatar' => $path,
            ]
        );

        User::find($user->id)->update([
                'username' => $data['username'],
        ]);

        return redirect()->route('profile.index');
    }
}
