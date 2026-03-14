<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;

use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    /**
     * 会員登録機能
     */
    use RefreshDatabase;

    /** @test
     * 名前が入力されていない場合、バリデーションメッセージが表示される
     */
    public function usernameIsRequired()
    {
        $response = $this->post('/register', [
            'username' => '',
            'email' => 'sample@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'username' => 'お名前を入力してください',
        ]);
    }

    /**
     * @test
     * メールアドレスが入力されていない場合、バリデーションメッセージが表示される
     */
    public function emailIsRequired()
    {
        $response = $this->post('/register', [
            'username' => 'sampleuser',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /**
     * @test
     * パスワードが入力されていない場合、バリデーションメッセージが表示される
     */
    public function passwordIsRequired()
    {
        $response = $this->post('/register', [
            'username' => 'sampleuser',
            'email' => 'sample@example.com',
            'password' => '',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /**
     * @test
     * パスワードが7文字以下の場合、バリデーションメッセージが表示される
     */
    public function passwordMustBeAtLeast8Characters()
    {
        $response = $this->post('/register', [
            'username' => 'sampleuser',
            'email' => 'sample@example.com',
            'password' => str_repeat('a', 7),
            'password_confirmation' => str_repeat('a', 7),
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    /**
     * @test
     * パスワードが確認用パスワードと一致しない場合、バリデーションメッセージが表示される
     */
    public function passwordConfirmationMustMatch()
    {
        $response = $this->post('/register', [
            'username' => 'sampleuser',
            'email' => 'sample@example.com',
            'password' => str_repeat('a', 8),
            'password_confirmation' => str_repeat('A', 8),
        ]);

        $response->assertSessionHasErrors(['password' => 'password_confirmed']);

        /*
        |--------------------------------------------------------------------------
        | Blade表示仕様（参考）
        |--------------------------------------------------------------------------
        | password_confirmed エラー時
        |
        | @error('password')
        |     @if ($message === 'password_confirmed')
        |         パスワードと一致しません
        |     @endif
        | @enderror
        */

    }

    /**
     * @test
     * 全ての項目が入力されている場合、会員情報が登録され、プロフィール設定画面に遷移される
     */
    public function canRegisterAndRedirectToProfile()
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

        // 会員情報が登録されている
        $this->assertDatabaseHas('users', [
            'username' => 'sampleuser',
            'email' => 'sample@example.com',
        ]);

        // メール認証
        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );
        $response = $this->get($verifyUrl);

        // 認証完了したのでプロフィール設定画面に遷移している
        $response->assertRedirect(route('profile.edit'));
    }
}