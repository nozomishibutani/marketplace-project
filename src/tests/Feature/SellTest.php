<?php

namespace Tests\Feature;


use App\Models\Item;
use App\Models\User;
use App\Models\Order;
use App\Models\Profile;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;


class SellTest extends TestCase
{
    /**
     * 出品商品情報登録機能
     *
     */
    use RefreshDatabase;

    /**
     * @test
     * 「商品出品画面にて必要な情報が保存できること（カテゴリ、商品の状態、商品名、ブランド名、商品の説明、販売価格）
     */
    public function itemDetailCanBeStored()
    {
        // ユーザーを作成してログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        // プロフィール登録
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        // ハイフンあり郵便番号にする
        $postcode = substr($profile->postcode, 0, 3) . '-' . substr($profile->postcode, 3);
        // ログインしているユーザーを確認
        $this->assertAuthenticatedAs($user);
        // カテゴリを作成
        $category = Category::factory()->create();
        // フェイクのストレージを作成
        Storage::fake('public');
        // フェイクの画像を作成
        $file = UploadedFile::fake()->image('dummy.png');
        // 出品
        $response = $this->post(route('items.store'), [
            'user_id' => $user->id,
            'name' => 'テストname',
            'brand_name' => 'テストbrand_name',
            'description' => 'テスト商品説明',
            'price' => 10000,
            'condition' => Item::CONDITION_BAD,
            'img' => $file,
            'categories' => [$category->id],
        ]);

        $response->assertRedirect(route('profile.index', ['page' => \App\Common\Common::PAGE_SELL]));

        // DBに値が登録されているか
        $this->assertDatabaseHas('items', [
            'user_id' => $user->id,
            'name' => 'テストname',
            'brand_name' => 'テストbrand_name',
            'description' => 'テスト商品説明',
            'price' => 10000,
            'condition' => Item::CONDITION_BAD,
        ]);
        // 実際にファイルが存在するか確認
        $item = Item::where('user_id', $user->id)->first();
        Storage::disk('public')->assertExists($item->img);

        // 商品カテゴリーが登録されているか
        $this->assertDatabaseHas('category_item', [
            'item_id' => $item->id,
            'category_id' => $category->id,
        ]);

    }
}
