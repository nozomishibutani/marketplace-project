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

            // 認証ページにリダイレクト
            return redirect()->route('verification.notice')
                            ->with('email-verification', 'メール認証が完了していません。<br>再送信して認証を完了してください。');
        }

        return $next($request);
    }
}
