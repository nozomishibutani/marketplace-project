<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use App\Models\User;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Category;
use App\Models\Profile;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
    public function itemDetailIsDisplayed() {
        // ユーザーを作成
        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);

        // フェイクの商品画像を作成
        Storage::fake('public');
        $file = UploadedFile::fake()->image('dummy.png');

        // 商品を作成
        $item = $this->createItemWithCategory(Item::STATUS_ON_SALE, $file);

        // いいね数
        $expectedFavoriteCount = Favorite::where('item_id', $item->id)->count(); // 0

        // コメントする
        Comment::factory(1)->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
        // コメント数
        $expectedCommentCount = Comment::where('item_id', $item->id)->count(); // 1

        $response = $this->get(route('items.show', $item->id));
        $response->assertStatus(200);

        // 商品画像
        $response->assertSee($item->img, false);
        // 商品名
        $response->assertSeeText($item->name);
        // ブランド名
        $response->assertSeeText($item->brand_name);
        // 価格
        $price = str_replace($item->price, '', ','); // カンマ消す
        $response->assertSeeText($price);
        // いいね数
        $this->assertEquals(0, $expectedFavoriteCount);
        $response->assertSee('<span class="item__actions-count">'. $expectedFavoriteCount .'</span>', false);
        // コメント数
        $this->assertEquals(1,$expectedCommentCount);
        $response->assertSee('<span>('. $expectedCommentCount .')</span>', false);
        // 商品説明
        $response->assertSeeText($item->detail);
        // 商品情報(カテゴリ)
        $response->assertSeeText($item->categories->first()->name);
        // 商品情報(商品の状態)
        $response->assertSeeText($item->condition);
        // コメントしたユーザー情報
        $response->assertSeeText($user->name);
        // コメント内容
        $response->assertSeeText($item->content);
    }

    /**
     * @test
     * 複数選択されたカテゴリが表示されているか
     */
    public function multipleSelectedCategoriesAreDisplayed() {
        // 商品を作成
        $item = Item::factory()->create([
            'status' => Item::STATUS_SOLD,
        ]);
        // カテゴリを複数作成
        $categories = Category::factory()->count(5)->create();
        $item->categories()->attach($categories->pluck('id'));
        $this->assertGreaterThanOrEqual(2, $item->categories()->count());

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

    /**
     * @test
     * 商品作成
     */
    protected function createItemWithCategory($status, $file) {
        $item = Item::factory()->create([
            'status' => $status,
            'img' => $file,
        ]);

        $category = Category::factory()->create();
        $item->categories()->attach($category->id);

        return $item;
    }
}
