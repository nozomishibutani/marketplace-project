<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use App\Models\Profile;
use App\Models\Order;
use Tests\TestCase;
use App\Common\Common;

class ProfileTest extends TestCase
{
    /**
     * ユーザー情報取得
     * ユーザー情報変更
     */
    use RefreshDatabase;

    /**
     * @test
     * 必要な情報が取得できる（プロフィール画像、ユーザー名、出品した商品一覧、購入した商品一覧）
     */
    public function userDetailIsDisplayed()
    {
        // ユーザーを作成してログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        // ログインしているユーザーを確認
        $this->assertAuthenticatedAs($user);
        // プロフィール登録
        $profile = Profile::factory()->create([
                'user_id' => $user->id,
                'postcode' => '1234567',
                'address' => 'テスト住所',
                'building' => 'テスト建物名',
                'avatar' => 'dummy.jpeg',
            ]);

        // 他のユーザー
        $otherUser = User::factory()->create();
        // 商品を作成
        $sellItem = Item::factory()->create([
            'user_id' => $user->id,
            'status' =>Item::STATUS_ON_SALE,
            ]);
        $buyItem = Item::factory()->create([
            'user_id' => $otherUser->id,
            'status' =>Item::STATUS_SOLD]);
        // 購入済みにする
        Order::factory()->create([
            'user_id' => $user->id,
            'item_id' => $buyItem->id,
        ]);
        // カテゴリを作成
        $category = Category::factory()->create();
        $sellItem->categories()->attach($category->pluck('id'));
        $buyItem->categories()->attach($category->pluck('id'));

        // マイページに遷移
        $response = $this->get(route('profile.index'));
        $response->assertStatus(200);

        // プロフィール画像
        $response->assertSee($profile->avatar, false); // デザイン修正後確認
        // ユーザー名
        $response->assertSeeText($user->username);

        // 出品
        // プロフィールページのデフォルトは出品した商品が表示される
        $response->assertSeeText($sellItem->name);
        $response->assertSee($sellItem->img, false);

        // 購入
        $response = $this->get(route('profile.index', ['page' => Common::PAGE_BUY]));
        $response->assertStatus(200);

        $response->assertSee($profile->avatar, false); // デザイン修正後確認
        $response->assertSeeText($user->username);
        $response->assertSeeText($buyItem->name);
        $response->assertSee($buyItem->img, false);

        // 出品した商品へ遷移
        $response = $this->get(route('profile.index', ['page' => Common::PAGE_SELL]));
        $response->assertStatus(200);

        $response->assertSee($profile->avatar, false); // デザイン修正後確認
        $response->assertSeeText($user->username);
        $response->assertSeeText($sellItem->name);
        $response->assertSee($sellItem->img, false);
    }

    /**
     * @test
     * 変更項目が初期値として過去設定されていること（プロフィール画像、ユーザー名、郵便番号、住所）
     */
    public function editProfilePageHasDefaultValues()
    {
        // ユーザーを作成してログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        // ログインしているユーザーを確認
        $this->assertAuthenticatedAs($user);
        // プロフィール登録
        $profile = Profile::factory()->create([
                'user_id' => $user->id,
                'postcode' => '123-4567',
                'address' => 'テスト住所',
                'building' => 'テスト建物名',
                'avatar' => 'dummy.jpeg',
            ]);

        // プロフィール編集画面に遷移
        $response = $this->get(route('profile.edit'));
        $response->assertStatus(200);
        // 登録データがフォームに表示されるか
        $response->assertSee($profile->avatar, false); // デザイン修正後確認
        $response->assertSee($user->username, false);
        $response->assertSee($profile->postcode, false);
        $response->assertSee($profile->address, false);
        $response->assertSee($profile->building, false);
    }
}
