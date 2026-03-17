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
    public function canLogout() {
        // 1. ユーザーにログインをする
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. ログアウトボタンを押す
        $response = $this->post('/logout');

        // ログアウト処理が実行される
        $this->assertGuest();
        $response->assertRedirect(route('items.index'));
    }

}
