<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmailVerificationTest extends TestCase
{
    /**
     * 会員登録機能
     */
    use RefreshDatabase;

    /** @test
     * 会員登録後、認証メールが送信される
     */
    public function sendVerificationEmailAfterRegistration()
    {
        // NotificationをFakeにする
        Notification::fake();

        $this->post('/register', [
            'username' => 'sampleuser',
            'email' => 'sample@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // 送信されたか検証
        $user = User::where([
            'username' => 'sampleuser',
            'email' => 'sample@example.com',
        ])->first();
        Notification::assertSentTo($user, VerifyEmail::class);

    }

    /**
     * @test
     * メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
     */
    public function redirectToVerificationSite()
    {
        // NotificationをFakeにする
        Notification::fake();

        $response = $this->post('/register', [
            'username' => 'sampleuser',
            'email' => 'sample@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // メール認証誘導画面にリダイレクト
        $response->assertRedirect(route('verification.notice'));

        // 「認証はこちらから」ボタンを押下
        $response = $this->get(route('verification.confirm'));
        // メール認証サイトに遷移
        $response->assertStatus(200)
            ->assertViewIs('auth.confirm')
            ->assertSee('Please click the button below to verify your email address.');
    }

    /**
     * @test
     * メール認証サイトのメール認証を完了すると、プロフィール設定画面に遷移する
     */
    public function redirectToProfileSetupAfterVerification()
    {
        Notification::fake();

        $response = $this->post('/register', [
        'username' => 'sampleuser',
        'email' => 'sample@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        ]);

        // 登録ユーザー取得
        $user = User::where([
            'username' => 'sampleuser',
            'email' => 'sample@example.com',
        ])->first();

        // 認証リンクを作成
        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );
        // 認証リンクにアクセス
        $response = $this->actingAs($user)->get($verifyUrl);

        // メール認証完了
        $this->assertNotNull($user->fresh()->email_verified_at);

        // 認証完了したのでプロフィール設定画面に遷移
        $response->assertRedirect(route('profile.edit'));
    }
}