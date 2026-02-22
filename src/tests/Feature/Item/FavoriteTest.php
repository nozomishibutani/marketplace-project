<?php

namespace Tests\Feature\Item;


use Tests\TestCase;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use App\Models\Favorite;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FavoriteTest extends TestCase
{
    /**
     * いいね機能
     */
    use RefreshDatabase;

    /**
     * @test
     * いいねアイコンを押下することによって、いいねした商品として登録することができる。
     */
    public function favoriteIsRegistered(){

        // ユーザーを作成してログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        // ログインしているユーザーを確認
        $this->assertAuthenticatedAs($user);
        // 商品を作成
        $item = Item::factory()->create(['status' => Item::STATUS_ON_SALE]);
        // カテゴリを作成
        $category = Category::factory()->create();
        $item->categories()->attach($category->pluck('id'));
        //  いいね数を確認
        $beforeCount = Favorite::count();
        // いいねする
        $response = $this->get(route('items.favorite', $item->id));

        $response->assertRedirect(route('items.show', $item->id));
        // いいね数が増えている
        $this->assertEquals($beforeCount + 1, Favorite::count());
    }

    /**
     * @test
     * 追加済みのアイコンは色が変化する
     */
    public function favoriteIconChangesColor(){

        // ユーザーを作成してログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        // ログインしているユーザーを確認
        $this->assertAuthenticatedAs($user);
        // 商品を作成
        $item = Item::factory()->create(['status' => Item::STATUS_ON_SALE]);
        // カテゴリを作成
        $category = Category::factory()->create();
        $item->categories()->attach($category->pluck('id'));

        $response = $this->get(route('items.show', $item->id));
        $response->assertStatus(200);

        // いいねなし
        $response->assertSee('heart_default.png', false); // デザイン修正後確認
        // いいねする
        $response = $this->get(route('items.favorite', $item->id));
        $response->assertRedirect(route('items.show', $item->id));

        $response = $this->get(route('items.show', $item->id));
        $response->assertStatus(200);

        // いいね画像が変わっているか
        $response->assertDontSee('heart_default.png', false); // デザイン修正後確認
        $response->assertSee('heart_pink.png', false); // デザイン修正後確認
    }

    /**
     * @test
     * 再度いいねアイコンを押下することによって、いいねを解除することができる。
     */
    public function favoriteIsCanceled(){

        // ユーザーを作成してログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        // ログインしているユーザーを確認
        $this->assertAuthenticatedAs($user);
        // 商品を作成
        $item = Item::factory()->create(['status' => Item::STATUS_ON_SALE]);
        // カテゴリを作成
        $category = Category::factory()->create();
        $item->categories()->attach($category->pluck('id'));

        // いいねする
        $response = $this->get(route('items.favorite', $item->id));
        // いいね数を確認
        $beforeCount = Favorite::count();
        $response->assertRedirect(route('items.show', $item->id));
        // いいね解除する
        $response = $this->get(route('items.unfavorite', $item->id));
        $response->assertRedirect(route('items.show', $item->id));

        //  いいね数が減少している
        $this->assertEquals($beforeCount - 1, Favorite::count());

    }
}
