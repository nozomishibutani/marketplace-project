<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;

class LogoutTest extends TestCase
{
    /**
     * @test
     * ログアウト機能
     */
    public function canLogout()
    {
        // ユーザーを作成してログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        // ログインしているユーザーを確認
        $this->assertAuthenticatedAs($user);
        // ログアウト
        $response = $this->post('/logout');
        // リダイレクト確認
        $response->assertRedirect(route('items.index'));
        // 現在のユーザーが認証されていない状態であること
        $this->assertGuest();
    }

}
