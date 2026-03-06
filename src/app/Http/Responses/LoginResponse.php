<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        // ログインしていて、メール未認証の場合
        if ($user && !$user->hasVerifiedEmail()) {

            // メール再送
            $user->sendEmailVerificationNotification();
            return redirect()->route('verification.notice');
        }

        // プロフィール未登録なら
        if (!$user->profile) {
            return redirect()->route('profile.edit');
        }

        return redirect()->intended(config('fortify.home'));
    }
}