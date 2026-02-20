<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;

class LogoutTest extends TestCase
{
    /**
     * ログアウト機能
     *
     * @return void
     */
    public function testLogout()
    {

        // ユーザーを作成してログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        // ログアウト
        $response = $this->post('/logout');
        // リダイレクト確認
        $response->assertRedirect(route('items.index'));
        // 現在のユーザーが認証されていない状態であること
        $this->assertGuest();
    }

}
