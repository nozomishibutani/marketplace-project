<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use App\Models\User;
use App\Models\Favorite;
use App\Models\Category;
use App\Models\Profile;
use Tests\TestCase;
use App\Common\Common;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetMyListTest extends TestCase

{
    /**
     * マイリスト一覧取得
     */
    use RefreshDatabase;

    /**
     * @test
     * いいねした商品だけが表示される
     */
    public function onlyFavoriteItemIsDisplayed() {
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // 商品を作成していいね済みにする
        $item = $this->createItemWithCategory(Item::STATUS_SOLD);
        Favorite::factory()->create([
                'user_id' => $user->id,
                'item_id' => $item->id,
            ]);

        // マイリストにアクセス
        $response = $this->get(route('items.index', ['tab' => Common::TAB_MYLIST]));
        $response->assertStatus(200);

        $response->assertSeeText($item->name);
    }

    /**
     * @test
     * 購入済み商品は「Sold」と表示される
     */
    public function purchasedItemDisplaysSold() {
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // 商品を作成していいね済みにする
        $item = $this->createItemWithCategory(Item::STATUS_SOLD);
        Favorite::factory()->create([
                'user_id' => $user->id,
                'item_id' => $item->id,
            ]);

        // マイリストにアクセス
        $response = $this->get(route('items.index', ['tab' => Common::TAB_MYLIST]));
        $response->assertStatus(200);

        // 売り切れ
        $response->assertSeeText($item->name);
        $response->assertSeeText('Sold');
    }

    /**
     * @test
     * 未認証の場合は何も表示されない
     */
    public function guestUserCannotSeeItem() {
        // 未ログイン状態
        $this->assertGuest();
        // マイリストに遷移
        $response = $this->get(route('items.index', ['tab' => Common::TAB_MYLIST]));
        $response->assertStatus(200);

        // 商品を作成していいね済みにする
        $item = $this->createItemWithCategory(Item::STATUS_ON_SALE);
        Favorite::factory()->create([
                'user_id' => $item->user_id,
                'item_id' => $item->id,
            ]);

        $response->assertDontSeeText($item->name);
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
