<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Illuminate\Support\Facades\Auth;

class RegisterResponse implements RegisterResponseContract
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