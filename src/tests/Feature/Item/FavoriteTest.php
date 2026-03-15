<?php

namespace Tests\Feature\Item;


use Tests\TestCase;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use App\Models\Favorite;
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
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // 商品を作成
        $item = $this->createItemWithCategory(Item::STATUS_ON_SALE);

        //  いいね数を確認
        $beforeCount = Favorite::count();

        // 商品詳細ページを開く
        $response = $this->get(route('items.show', $item->id));
        $response->assertStatus(200);

        // いいねアイコンを押下
        $response = $this->post(route('items.favorite', $item->id));
        $response->assertRedirect(route('items.show', $item->id));

        // いいね数が増えている
        $this->assertEquals($beforeCount + 1, Favorite::count());
        // DBにも登録がある
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    /**
     * @test
     * 追加済みのアイコンは色が変化する
     */
    public function favoriteIconChangesColor() {
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // 商品を作成
        $item = $this->createItemWithCategory(Item::STATUS_SOLD);

        // 商品詳細ページを開く
        $response = $this->get(route('items.show', $item->id));
        $response->assertStatus(200);

        // いいね前
        $response->assertDontSee('heart_pink.png', false);
        $response->assertSee('heart_default.png', false);

        // いいねする
        $this->post(route('items.favorite', $item->id))
            ->assertRedirect(route('items.show', $item->id));

        // いいね後
        $this->get(route('items.show', $item->id))
            ->assertSee('heart_pink.png', false)
            ->assertDontSee('heart_default.png', false);
    }

    /**
     * @test
     * 再度いいねアイコンを押下することによって、いいねを解除することができる。
     */
    public function favoriteIsCanceled() {
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // 商品を作成
        $item = $this->createItemWithCategory(Item::STATUS_SOLD);
        // いいね状態を作る
        Favorite::factory()->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // 商品詳細ページを開く
        $response = $this->get(route('items.show', $item->id));
        $response->assertStatus(200);

        // いいね数を確認
        $beforeCount = Favorite::count();

        // いいね解除する
        $response = $this->delete(route('items.unfavorite', $item->id));
        $response->assertRedirect(route('items.show', $item->id));

        //  いいね数が減少している
        $this->assertEquals($beforeCount - 1, Favorite::count());
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
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
