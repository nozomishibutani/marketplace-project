<?php

namespace Tests\Feature\Item;


use App\Models\Item;
use App\Models\User;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Category;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;


class PurchaseItemTest extends TestCase
{
    /**
     * 商品購入機能
     *
     */
    use RefreshDatabase;

    /**
     * @test
     * 「購入する」ボタンを押下すると購入が完了する
     */
    public function canPurchaseItem()
    {
        // ユーザーを作成
        $user = User::factory()->create();
        // 商品を作成
        $item = Item::factory()->create(['status' => Item::STATUS_ON_SALE,]);
        // カテゴリを作成
        $category = Category::factory()->create();
        $item->categories()->attach($category->pluck('id'));
        // 購入画面に遷移
        $response = $this->get(route('purchase.confirm', $item->id));
        $response->assertStatus(200);
        // 購入
        $response = $this->get(route('purchase', $item->id));



    }
}
