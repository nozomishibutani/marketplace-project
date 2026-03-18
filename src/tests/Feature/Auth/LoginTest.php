<?php

namespace Tests\Feature\Auth;

use App\Common\Common;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Profile;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    /**
     * ログイン機能
     */
    use RefreshDatabase;
    /**
     * @test
     * メールアドレスが入力されていない場合、バリデーションメッセージが表示される
     */
    public function emailIsRequired() {
        $this->createVerifiedUser();

        // 1. ログインページを開く
        $this->get('/login');

        // 2. メールアドレスを入力せずに他の必要項目を入力する
        // 3. ログインボタンを押す
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);
    }

    /**
     * @test
     * パスワードが入力されていない場合、バリデーションメッセージが表示される
     */
    public function passwordIsRequired() {
        $user = $this->createVerifiedUser();

        // 1. ログインページを開く
        $this->get('/login');

        // 2. パスワードを入力せずに他の必要項目を入力する
        // 3. ログインボタンを押す
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);
    }

    /**
     * @test
     * 入力情報が間違っている場合、バリデーションメッセージが表示される
     */
    public function loginFailsForNonExistentUser() {
        // 1. ログインページを開く
        $this->get('/login');

        // 2. 必要項目を登録されていない情報を入力する
        // 3. ログインボタンを押す
        $response = $this->post('/login', [
            'email' => 'notexist@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);
        // 認証されていないことを確認
        $this->assertGuest();
    }

    /**
     * @test
     * 正しい情報が入力された場合、ログイン処理が実行される
     */
    public function loginWithCorrectCredentials() {
        $user = $this->createVerifiedUser();

        // 1. ログインページを開く
        $this->get('/login');

        // 2. 全ての必要項目を入力する
        // 3. ログインボタンを押す
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // ログイン処理が実行される
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('items.index', ['tab' => Common::TAB_MYLIST ]));
    }

    /**
     * @test
     * ユーザー作成
     */
    protected function createVerifiedUser() {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);
        $user->markEmailAsVerified();
        Profile::factory()->create(['user_id' => $user->id]);
        return $user;
    }
}
