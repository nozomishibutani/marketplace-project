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
    public function canRegisterFavoriteByPushingIcon(){

        // ユーザーを作成
        $user = User::factory()->create();
        // 商品を作成
        $item = Item::factory()->create(['status' => Item::STATUS_ON_SALE]);
        // カテゴリを作成
        $category = Category::factory()->create();
        $item->categories()->attach($category->pluck('id'));
        // いいねがないことを確認
        $this->assertDatabaseCount('favorites', 0);
        // いいねする
        Favorite::factory()->create([
                'user_id' => $user->id,
                'item_id' => $item->id,
        ]);
        // いいね数
        $expectedFavoriteCount = Favorite::where([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ])->count();

        $response = $this->get(route('items.show', $item->id));
        $response->assertStatus(200);

        $this->assertEquals(1, $expectedFavoriteCount); // デザイン修正後確認
        $response->assertSee('<span>'. $expectedFavoriteCount .'</span>', false); // デザイン修正後確認
    }

    /**
     * @test
     * 追加済みのアイコンは色が変化する
     */
    public function pushedFavoriteIconChangesColor(){

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
        Favorite::factory()->create([
                'user_id' => $user->id,
                'item_id' => $item->id,
        ]);

        $response = $this->get(route('items.show', $item->id));
        $response->assertStatus(200);

        $response->assertDontSee('heart_default.png', false); // デザイン修正後確認
        $response->assertSee('heart_pink.png', false); // デザイン修正後確認
    }

    /**
     * @test
     * 再度いいねアイコンを押下することによって、いいねを解除することができる。
     */
    public function canCancelFavorite(){

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
        Favorite::factory()->create([
                'user_id' => $user->id,
                'item_id' => $item->id,
        ]);

        $response = $this->get(route('items.show', $item->id));
        $response->assertStatus(200);

        $response->assertSee('heart_pink.png', false); // デザイン修正後確認

        // いいねを解除
        Favorite::where([
                'user_id' => $user->id,
                'item_id' => $item->id,
        ])->delete();

        $response = $this->get(route('items.show', $item->id));
        $response->assertStatus(200);

        $response->assertSee('heart_default.png', false); // デザイン修正後確認
        $response->assertDontSee('heart_pink.png', false); // デザイン修正後確認
    }
}
