<?php

namespace Tests\Feature;


use App\Models\Item;
use App\Models\User;
use App\Models\Profile;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;
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
        // カテゴリを作成
        $category = Category::factory()->create();
        // フェイクのストレージを作成
        Storage::fake('public');
        // フェイクの画像を作成
        $file = UploadedFile::fake()->image('dummy.png');

        // 1. ユーザーにログインする
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // 2. 商品出品画面を開く
        $response = $this->get(route('items.create'));
        $response->assertStatus(200);

        // 3 . 各項目に適切な情報を入力して保存する
        $this->post(route('items.store'), [
            'user_id' => $user->id,
            'name' => 'テストname',
            'brand_name' => 'テストbrand_name',
            'description' => 'テスト商品説明',
            'price' => 10000,
            'condition' => Item::CONDITION_BAD,
            'img' => $file,
            'categories' => [$category->id],
        ]);

        // 各項目が正しく保存されている
        $this->assertDatabaseHas('items', [
            'user_id' => $user->id,
            'name' => 'テストname',
            'brand_name' => 'テストbrand_name',
            'description' => 'テスト商品説明',
            'price' => 10000,
            'condition' => Item::CONDITION_BAD,
        ]);

        // 画像ファイル
        $item = Item::where('user_id', $user->id)->first();
        Storage::disk('public')->assertExists($item->img);

        // 商品カテゴリー
        $this->assertDatabaseHas('category_item', [
            'item_id' => $item->id,
            'category_id' => $category->id,
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
        Profile::factory()->create([
            'user_id' => $user->id,
            ]);
        return $user;
    }
}
