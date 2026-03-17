<?php

namespace Tests\Feature\Item;

use Tests\TestCase;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;
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
    public function favoriteIsRegistered() {
        // 商品を作成
        $item = $this->createItemWithCategory(Item::STATUS_ON_SALE);

        //  いいね数を確認
        $beforeCount = $item->favorites()->count();

        // 1. ユーザーにログインする
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // 2. 商品詳細ページを開く
        $response = $this->get(route('items.show', $item->id));
        $response->assertStatus(200);

        // 3. いいねアイコンを押下
        $response = $this->post(route('items.favorite', $item->id));
        $response->assertRedirect(route('items.show', $item->id));

        // いいねした商品として登録され、いいね合計値が増加表示される
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
        $this->assertEquals($beforeCount + 1, $item->favorites()->count());
    }

    /**
     * @test
     * 追加済みのアイコンは色が変化する
     */
    public function favoriteIconChangesColor() {
        // 商品を作成
        $item = $this->createItemWithCategory(Item::STATUS_SOLD);

        // 1. ユーザーにログインする
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // 2. 商品詳細ページを開く
        $response = $this->get(route('items.show', $item->id));
        $response->assertStatus(200);

        // いいね前
        $response->assertDontSee('heart_pink.png', false);
        $response->assertSee('heart_default.png', false);

        // 3. いいねアイコンを押下
        $this->post(route('items.favorite', $item->id))
            ->assertRedirect(route('items.show', $item->id));

        // いいねアイコンが押下された状態では色が変化する
        $this->get(route('items.show', $item->id))
            ->assertSee('heart_pink.png', false)
            ->assertDontSee('heart_default.png', false);
    }

    /**
     * @test
     * 再度いいねアイコンを押下することによって、いいねを解除することができる。
     */
    public function favoriteIsCanceled() {
        // 商品を作成
        $item = $this->createItemWithCategory(Item::STATUS_SOLD);

        // 1. ユーザーにログインする
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // いいね状態を作る
        $user->favorites()->attach($item->id);

        // 2. 商品詳細ページを開く
        $response = $this->get(route('items.show', $item->id));
        $response->assertStatus(200);

        // いいね数を確認
        $beforeCount = $item->favorites()->count();

        // 3. いいねアイコンを押下
        $response = $this->delete(route('items.unfavorite', $item->id));
        $response->assertRedirect(route('items.show', $item->id));

        // いいねが解除され、いいね合計値が減少表示される
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
        $this->assertEquals($beforeCount - 1, $item->favorites()->count());
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

    /**
     * @test
     * 商品作成
     */
    protected function createItemWithCategory($status) {
        $item = Item::factory()->create([
            'status' => $status,
        ]);

        $category = Category::factory()->create();
        $item->categories()->attach($category->id);

        return $item;
    }
}
