<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;

use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    /**
     * 会員登録機能
     *
     */
    use RefreshDatabase;

    /**
     * @test
     * バリデーションメッセージが表示される
     */
    public function validationErrorsAreDisplayed()
    {
        // 名前が入力されていない場合、バリデーションメッセージが表示される
        $response = $this->post('/register', [
        'username' => '',
        'email' => 'sample@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            // 実際のエラー文言と異なるとFAILURES!になる
            'username' => 'ユーザー名を入力してください',
        ]);

        // メールアドレスが入力されていない場合、バリデーションメッセージが表示される
        $response = $this->post('/register', [
        'username' => 'sampleuser',
        'email' => '',
        'password' => 'password',
        'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);

        // パスワードが入力されていない場合、バリデーションメッセージが表示される
        $response = $this->post('/register', [
        'username' => 'sampleuser',
        'email' => 'sample@example.com',
        'password' => '',
        'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);

        // パスワードが7文字以下の場合、バリデーションメッセージが表示される
        $response = $this->post('/register', [
        'username' => 'sampleuser',
        'email' => 'sample@example.com',
        'password' => str_repeat('a', 7),
        'password_confirmation' => str_repeat('a', 7),
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);

        // パスワードが確認用パスワードと一致しない場合、バリデーションメッセージが表示される
        $response = $this->post('/register', [
        'username' => 'sampleuser',
        'email' => 'sample@example.com',
        'password' => str_repeat('a', 8),
        'password_confirmation' => str_repeat('A', 8),
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません',
        ]);
    }

    /**
     * @test
     * 全ての項目が入力されている場合、会員情報が登録され、プロフィール設定画面に遷移される
     */
    public function canRegisterAndRedirectToProfile(){

        // メール送信を抑制
        Notification::fake();

        $response = $this->post('/register', [
        'username' => 'sampleuser',
        'email' => 'sampleuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        ]);

        // 登録ユーザー取得
        $user = User::where('email', 'sampleuser@example.com')->first();

        // メール認証画面認に遷移
        $response->assertRedirect(route('verification.notice'));

        // 認証リンクを作成
        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 認証リンクにアクセス
        $this->actingAs($user)->get($verifyUrl);

        // email_verified_at がセットされているか確認
        $this->assertNotNull($user->fresh()->email_verified_at);

        // 認証済みなのでプロフィール画面に遷移している
        $response = $this->actingAs($user)->get(route('profile.edit'));
        $response->assertStatus(200);

        // 会員情報が登録されている
        $this->assertDatabaseHas('users', [
            'username' => 'sampleuser',
            'email' => 'sampleuser@example.com',
        ]);
    }
}