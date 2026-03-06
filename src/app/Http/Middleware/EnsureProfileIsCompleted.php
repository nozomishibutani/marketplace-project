<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use function Laravel\Prompts\alert;

class EnsureProfileIsCompleted
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

        $user = auth()->user();

        // プロフィール未登録
        if (!$user->profile) {
            return redirect()->route('profile.edit')->with('alert','プロフィールが未登録です。登録完了すると全ての機能が使えるようになります。');
        }

        return $next($request);

    }
}
