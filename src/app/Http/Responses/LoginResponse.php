<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        // プロフィール未登録なら
        if (!$user->profile) {
            return redirect()->route('profile.edit');
        }

        return redirect()->intended(config('fortify.home'));
    }
}