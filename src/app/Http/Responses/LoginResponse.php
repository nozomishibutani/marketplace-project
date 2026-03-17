<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // メール未認証の場合
        if ($user && !$user->hasVerifiedEmail()) {
            // メール再送
            $user->sendEmailVerificationNotification();
            auth()->logout();
            session(['unverified_user_id' => $user->id]);
            return redirect()->route('verification.notice');
        }

        // プロフィール未登録の場合
        if (!$user->profile) {
            return redirect()->route('profile.edit');
        }

        return redirect()->intended(config('fortify.home'));
    }
}