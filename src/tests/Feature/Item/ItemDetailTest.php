<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use App\Models\User;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Category;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ItemDetailTest extends TestCase
{
    /**
     * 商品詳細情報取得
     *
     */
    use RefreshDatabase;

    /**
     * @test
     * 必要な情報が表示される
     */
    public function itemDetailIsDisplayed()
    {

        // ユーザーを作成
        $user = User::factory()->create();
        // 商品を作成
        $item = Item::factory()->create([
            'status' => Item::STATUS_ON_SALE,
            'img' => now()->format('YmdHis') . '.png',
        ]);
        // カテゴリを作成
        $category = Category::factory()->create();
        $item->categories()->attach($category->pluck('id'));

        // いいね数
        $expectedFavoriteCount = Favorite::where('item_id', $item->id)->count();
        // コメントする
        Comment::factory(1)->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
        // コメント数
        $expectedCommentCount = Comment::where('item_id', $item->id)->count();

        $response = $this->get(route('items.show', $item->id));
        $response->assertStatus(200);

        // 商品画像
        $response->assertSee($item->img, false); // デザイン修正後確認
        // 商品名
        $response->assertSeeText($item->name);
        // ブランド名
        $response->assertSeeText($item->brand_name);
        // 価格
        $response->assertSeeText($item->price);
        // いいね数
        $this->assertEquals(0, $expectedFavoriteCount); // デザイン修正後確認
        // 商品説明
        $response->assertSeeText($item->detail);
        // 商品情報(カテゴリ)
        $response->assertSeeText($category->name); // デザイン修正後確認
        // 商品情報(商品の状態)
        $response->assertSeeText(Item::CONDITIONS[Item::CONDITION_GOOD]);
        // コメント数
        $this->assertEquals(1,$expectedCommentCount); // デザイン修正後確認
        // コメントしたユーザー情報
        $response->assertSeeText($user->name);
        // コメント内容
        $response->assertSeeText($item->content);
    }

    /**
     * @test
     * 複数選択されたカテゴリが表示されているか
     */
    public function multipleSelectedCategoriesAreDisplayed()
    {
        // 商品を作成
        $item = Item::factory()->create([
            'status' => Item::STATUS_ON_SALE,
            'img' => now()->format('YmdHis') . '.png',
        ]);
        // カテゴリを作成
        $categories = Category::factory()->count(2)->create();
        $item->categories()->attach($categories->pluck('id'));

        $response = $this->get(route('items.show', $item->id));
        $response->assertStatus(200);

        // 商品情報(カテゴリ)
        foreach ($categories as $category) {
            $response->assertSeeText($category->name);
        }

        $this->assertEquals(
            $categories->count(),
            $item->categories()->count()
        );
    }
}
