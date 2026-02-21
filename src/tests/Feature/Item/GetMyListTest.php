<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use App\Models\User;
use App\Models\Favorite;
use Tests\TestCase;
use App\Common\Common;
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
    public function onlyFavoriteItemsAreDisplayed()
    {
        // ユーザーを作成してログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        // ログインしているユーザーを確認
        $this->assertAuthenticatedAs($user);

        // 商品を作成していいねする
        $item = Item::factory()->create(['status' =>Item::STATUS_ON_SALE]);
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
    public function purchasedItemDisplaysSold()
    {
        // ユーザーを作成してログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        // ログインしているユーザーを確認
        $this->assertAuthenticatedAs($user);

        // 商品を作成していいねする
        $item = Item::factory()->create(['status' => item::STATUS_SOLD]);
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
    public function guestUserCannotSeeItems()
    {
        // 未ログイン状態
        $this->assertGuest();
        // マイリストに遷移
        $response = $this->get(route('items.index', ['tab' => Common::TAB_MYLIST]));
        $response->assertStatus(200);

        // 商品を作成していいねする
        $item = Item::factory()->create(['status' => item::STATUS_ON_SALE]);
        Favorite::factory()->create([
                'user_id' => $item->user_id,
                'item_id' => $item->id,
            ]);

        $response->assertDontSeeText($item->name);
    }
}
