<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
        $this->assertTrue(true);

        $response = $this->post('/register', [
        'username' => '',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            // 実際のエラー文言と異なるとFAILURES!になる
            'username' => 'お名前を入力してください',
        ]);

        // 確認コマンド
        //vendor/bin/phpunit tests/Feature/Auth/RegisterTest.php
    }
}