<?php

namespace Tests\Feature\Item;

use Tests\TestCase;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetItemListTest extends TestCase
{
    /**
     * 商品一覧取得
     */
    use RefreshDatabase;

    /**
     * @test
     * 全商品を取得できる
     */
    public function allItemsAreDisplayed() {

        // 商品作成
        $item[Item::STATUS_ON_SALE] = $this->createItemWithCategory(Item::STATUS_ON_SALE);
        $item[Item::STATUS_SOLD] = $this->createItemWithCategory(Item::STATUS_SOLD);

        // 期待する表示件数
        $response = $this->get(route('items.index'));
        $response->assertStatus(200);
        $this->assertEquals(
            2,
            Item::all()->count()
        );

        // 表示されている商品
        $response->assertSeeText($item[Item::STATUS_ON_SALE]->name);
        $response->assertSeeText($item[Item::STATUS_SOLD]->name);
    }

    /**
     * @test
     * 購入済み商品は「Sold」と表示される
     */
    public function purchasedItemDisplaysSold() {
        // 購入済み商品を作成する
        $item = $this->createItemWithCategory(Item::STATUS_SOLD);

        // Sold表示
        $response = $this->get(route('items.index'));
        $response->assertStatus(200);
        $response->assertSeeText($item->name);
        $response->assertSeeText('Sold');
    }

    /**
     * @test
     * 自分が出品した商品は表示されない
     */
    public function ownItemIsNotDisplayed() {

        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $items = array();

        foreach (Item::STATUSES as $status) {
            $items[$status] = Item::factory()->create([
                'user_id' => $user->id,
                'status' => $status,
            ]);
            $category = Category::factory()->create();
            $items[$status]->categories()->attach($category->id);
        }

        $response = $this->get(route('items.index'));
        $response->assertStatus(200);
        // 表示されない
        foreach ($items as $item) {
            $response->assertDontSeeText($item->name);
        }
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