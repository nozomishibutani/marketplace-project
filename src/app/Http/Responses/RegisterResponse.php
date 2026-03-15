<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Illuminate\Support\Facades\Auth;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 新規登録後メール認証へ遷移
        if ($user && !$user->hasVerifiedEmail()) {
            session(['unverified_user_id' => $user->id]);
            auth()->logout();
            return redirect()->route('verification.notice');
        }

        return redirect()->intended(config('fortify.home'));
    }
}