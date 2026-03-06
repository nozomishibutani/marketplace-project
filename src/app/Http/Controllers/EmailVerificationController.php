<?php

namespace App\Http\Controllers;



use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;



class EmailVerificationController extends Controller
{
    // 未認証ユーザー向けの誘導画面
    public function notice()
    {
        return view('auth.verify_notice');
    }

    /**
     * テスト用メール認証処理
     */
    public function verify()
    {
        $user = Auth::user();

        if (!$user->hasVerifiedEmail()) {
            // email_verified_at に日時セット
            $user->markEmailAsVerified();
        }

        return redirect()->route('profile.edit');
    }

    public function resend(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', '認証メールを再送信しました');
    }

    public function confirm()
    {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => auth()->id(),
                'hash' => sha1(auth()->user()->email),
            ]
        );

        return view('auth.verify_confirm', compact('url'));
    }
}
