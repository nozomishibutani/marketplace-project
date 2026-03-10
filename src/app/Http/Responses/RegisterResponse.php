<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Illuminate\Support\Facades\Auth;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        // 新規登録後メール認証へ遷移
        if ($user && !$user->hasVerifiedEmail()) {

            return redirect()->route('verification.notice');
        }

        return redirect()->intended(config('fortify.home'));

    }
}