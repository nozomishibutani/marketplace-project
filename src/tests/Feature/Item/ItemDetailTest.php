<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use App\Models\User;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Category;
use Tests\TestCase;

class ItemDetailTest extends TestCase
{
    /**
     * 商品詳細情報取得
     *
     * @return void
     */
    public function testItemDetail()
    {
        // 現在時刻取得
        $now = now()->format('YmdHis');

        // ------------------------
        // 必要な情報が表示される
        // ------------------------
        // 未ログイン
        // 出品中
        // コメント・いいねあり
        // ------------------------

        // ユーザー作成
        $user = User::factory()->create();

        // カテゴリ
        $category = Category::factory()->create([
            'name' => 'カテゴリ' . $now,
        ]);

        // 商品作成
        $activeItem = Item::factory()->create([
            'status' => Item::STATUS_ON_SALE, // 出品中
            'name' => 'テスト商品' . $now,
            'brand_name' => 'Apple' . $now,
            'price' => 10000,
            'category_id' => $category->id,
            'description' => '説明テスト' . $now,
            'condition' => Item::CONDITION_GOOD,
            'img' => $now . 'png',
        ]);

        // いいねする
        Favorite::factory()->create([
            'user_id' => $user->id,
            'item_id' => $activeItem->id,
        ]);
        // いいね数
        $expectedFavoriteCount = Favorite::where('item_id', $activeItem->id)->count();

        // コメントする
        Comment::factory()->create([
            'user_id' => $user->id,
            'item_id' => $activeItem->id,
            'content' => 'コメントテスト' . $now,
        ]);
        // コメント数
        $expectedCommentCount = Comment::where('item_id', $activeItem->id)->count();

        $response = $this->get(route('items.show', $activeItem->id));
        $response->assertStatus(200);

        // 商品画像
        // 保留

        // 商品名
        $response->assertSeeText('テスト商品' . $now);
        // ブランド名
        $response->assertSeeText('Apple' . $now);
        // 価格
        $response->assertSeeText('10000');
        // いいね数 // ありなし
        $response->assertSeeText((string) $expectedFavoriteCount); // デザイン修正後再修正
        // 商品説明
        $response->assertSeeText('説明テスト' . $now);
        // 商品情報(カテゴリ、商品の状態)
        $response->assertSeeText('カテゴリ' . $now); // デザイン修正後再修正 // 複数カテゴリ時
        $response->assertSeeText(Item::CONDITIONS[Item::CONDITION_GOOD]);
        // コメント数 // コメント有り無しバージョン
        $response->assertSeeText((string) $expectedCommentCount); // デザイン修正後再修正
        // コメントしたユーザー情報
        $response->assertSeeText($user->name);
        // コメント内容
        $response->assertSeeText('コメントテスト' . $now);

        // [任意] Soldの表示はない
        // [任意] コメントボタン押下でログインページに遷移する
        // [任意] いいねボタン押下でログインページに遷移する
        // [任意] 購入ボタン押下でログインページに遷移する


        // ------------------------
        // 未ログイン
        // 売り切れ
        // コメント・いいねなし
        // ------------------------

        // カテゴリ
        $category = Category::factory()->create([
            'name' => 'カテゴリ2' . $now,
        ]);

        // 商品作成
        $activeItem = Item::factory()->create([
            'status' => Item::STATUS_SOLD , // 売り切れ
            'name' => 'テスト商品' . $now,
            'brand_name' => 'Banana' . $now,
            'price' => 9999,
            'category_id' => $category->id,
            'description' => '説明テスト' . $now,
            'condition' => Item::CONDITION_NO_DAMAGE,
            'img' => $now . 'png',
        ]);

        // いいね数
        //$expectedFavoriteCount = Favorite::where('item_id', $activeItem->id)->count();
        // コメント数
        //$expectedCommentCount = Comment::where('item_id', $activeItem->id)->count();

        $response = $this->get(route('items.show', $activeItem->id));
        $response->assertStatus(200);

        // 商品画像
        // 保留

        // 商品名
        $response->assertSeeText('テスト商品' . $now);
        // ブランド名
        $response->assertSeeText('Banana' . $now);
        // 価格
        $response->assertSeeText('9999');
        // いいね数
        //$response->assertSeeText((string) $expectedFavoriteCount); // デザイン修正後再修正
        // 商品説明
        $response->assertSeeText('説明テスト' . $now);
        // 商品情報(カテゴリ、商品の状態)
        $response->assertSeeText('カテゴリ2' . $now);
        $response->assertSeeText(Item::CONDITIONS[Item::CONDITION_NO_DAMAGE]);
        // コメント数
        //$response->assertSeeText((string) $expectedCommentCount); // デザイン修正後再修正
        // コメントしたユーザー情報
        //$response->assertSeeText($user->name);
        // コメント内容
        //$response->assertSeeText('コメントテスト' . $now);

        // [任意] Soldの表示があるか



        // ------------------------
        // ログイン済み
        // 出品中
        // ------------------------
        // [任意] 購入ボタン押下で購入画面に遷移する

        // ------------------------
        // ログイン済み
        // 売り切れ
        // ------------------------
        // [任意] 購入ボタンが表示されない
    }
}
