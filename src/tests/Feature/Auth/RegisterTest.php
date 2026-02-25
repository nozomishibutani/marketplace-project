<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
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
            'username' => 'お名前を入力してください',
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

        $response = $this->post('/register', [
        'username' => 'sampleuser',
        'email' => 'sampleuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        ]);

        // プロフィール画面に遷移
        $response->assertRedirect(route('profile.edit'));
        // 会員情報が登録されている
        $this->assertDatabaseHas('users', [
            'username' => 'sampleuser',
            'email' => 'sampleuser@example.com',
        ]);
    }
}