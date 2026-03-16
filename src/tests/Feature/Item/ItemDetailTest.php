<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use App\Models\User;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Category;
use App\Models\Profile;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
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
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

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

        // 1. 商品詳細ページを開く
        $response = $this->get(route('items.show', $item->id));
        $response->assertStatus(200);

        // すべての情報が商品詳細ページに表示されている
        // 商品画像
        $response->assertSee($item->img, false);
        // 商品名
        $response->assertSeeText($item->name);
        // ブランド名
        $response->assertSeeText($item->brand_name);
        // 価格
        $response->assertSeeText(str_replace($item->price, '', ','));
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
        $response->assertSeeText(Item::CONDITIONS[$item->condition]);
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
            'status' => Item::STATUS_SOLD,
        ]);

        // カテゴリを複数作成
        $categories = Category::factory()->count(5)->create();

        // 商品にカテゴリを紐づけ
        $item->categories()->attach($categories->pluck('id'));

        // 複数カテゴリであることを確認
        $this->assertGreaterThan(4, $item->categories->count());

        // 1. 商品詳細ページを開く
        $response = $this->get(route('items.show', $item->id));
        $response->assertStatus(200);

        // 複数カテゴリが表示されている
        foreach ($categories as $category) {
            $response->assertSeeText($category->name);
        }
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
