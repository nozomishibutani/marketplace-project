<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class RegisterTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testRegister()
    {
        // 文字列か確認するための配列
        $array = [
            'test' => ['array'],
        ];

        // === 必須課題のテスト ===

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

        // // 全ての項目が入力されている場合、会員情報が登録され、プロフィール設定画面に遷移される
        // $response = $this->post('/register', [
        // 'username' => now()->format('Y-m-d H:i:s'),
        // 'email' => now()->format('Y-m-d H:i:s') . '@example.com',
        // 'password' => 'password',
        // 'password_confirmation' => 'password',
        // ]);

        // $response->assertRedirect(route('items.index'));// プロフィール設定画面に遷移する※未実装


        // === 追加テスト（任意・自己学習） ===

        // === お名前バリデーションテスト ===
        // 既に同じお名前が登録されている場合、バリデーションメッセージが表示される
        $response = $this->post('/register', [
        'username' => 'testuser',
        'email' => 'sample@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'username' => 'このお名前は使用できません',
        ]);

        // お名前の入力が255文字を超えた場合、バリデーションメッセージが表示される
        $response = $this->post('/register', [
        'username' => str_repeat('a', 256),
        'email' => 'sample@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'username' => 'お名前は255文字以内で入力してください',
        ]);

        // お名前の入力が文字列ではない場合、バリデーションメッセージが表示される
        $response = $this->post('/register', [
        'username' => $array,
        'email' => 'sample@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'username' => 'お名前は文字列で入力してください',
        ]);

        // === メールアドレスバリデーションテスト ===
        // 既に同じメールアドレスが登録されている場合、バリデーションメッセージが表示される
        $response = $this->post('/register', [
        'username' => 'sampleuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'このメールアドレスは利用できません',
        ]);

    // メールアドレスの入力が255文字を超えた場合、バリデーションメッセージが表示される
        $response = $this->post('/register', [
        'username' => 'sampleuser',
        'email' => str_repeat('a', 256),
        'password' => 'password',
        'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスは255文字以内で入力してください',
        ]);

        // メールアドレスの入力形式ではない場合、バリデーションメッセージが表示される
        $response = $this->post('/register', [
        'username' => 'sampleuser',
        'email' => '1234567890.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスはメール形式で入力してください',
        ]);

        // === パスワードバリデーションテスト ===
        // パスワードの入力が文字列ではない場合、バリデーションメッセージが表示される
        $response = $this->post('/register', [
        'username' => 'sampleuser',
        'email' => 'sample@example.com',
        'password' => $array,
        'password_confirmation' => $array,
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは文字列で入力してください',
        ]);

        // パスワードの入力が255文字を超えた場合、バリデーションメッセージが表示される
        $response = $this->post('/register', [
        'username' => 'sampleuser',
        'email' => 'sample@example.com',
        'password' => str_repeat('a', 256),
        'password_confirmation' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは255文字以内で入力してください',
        ]);
    }
}