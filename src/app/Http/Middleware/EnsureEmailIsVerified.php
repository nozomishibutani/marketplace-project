<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // ログインしていて、メール未認証の場合
        if ($user && !$user->hasVerifiedEmail()) {

            // メール再送
            $user->sendEmailVerificationNotification();

            // 認証ページにリダイレクト
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
