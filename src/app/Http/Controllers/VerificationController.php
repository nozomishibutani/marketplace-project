<?php

namespace App\Http\Controllers;



use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;



class VerificationController extends Controller
{

    /**
     * このコントローラーは VerifyEmail 通知を利用したメール認証用です。
     * VerifyEmail 通知ではデフォルトで以下3つのルートを使用します：
     * 1. verification.notice      -> 確認メール送信後の画面
     * 2. verification.verify      -> メール内のリンククリックで確認完了
     * 3. verification.send        -> メール再送信
     *
     * ビューは自由にカスタマイズ可能ですが、ルート名は変更不可です。
     */

    // 未認証ユーザー向けの誘導画面
    public function notice()
    {
        return view('auth.notice');
    }

    /**
     * テスト用メール認証処理
     */
    public function verify()
    {

        /** @var \App\Models\User $user */
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

        return view('auth.confirm', compact('url'));
    }
}
