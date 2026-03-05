<?php

namespace App\Http\Controllers;


use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;


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
}
