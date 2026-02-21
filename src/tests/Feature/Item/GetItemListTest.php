<?php

namespace Tests\Feature\Item;

use Tests\TestCase;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetItemListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 全商品を取得できる
     */
    public function canGetAllItems(){

        // Itemテーブルに登録がないことを確認
        $this->assertDatabaseCount('items', 0);
        // 商品を作成する
        foreach(Item::STATUSES as $status){
            $item = Item::factory()->create(['status' => $status]);
            $name[$status] = $item->name;
        }

        $response = $this->get(route('items.index'));
        $response->assertStatus(200);

        // 期待する表示件数
        $this->assertEquals(
            2,
            Item::where('status', '!=', Item::STATUS_SUSPENDED)->count()
        );

        // 表示されている商品
        $response->assertSeeText($name[Item::STATUS_ON_SALE]);
        $response->assertSeeText($name[Item::STATUS_SOLD]);
        // 出品停止中は非表示
        $response->assertDontSeeText($name[Item::STATUS_SUSPENDED]);
    }

    /**
     * @test
     * 購入済み商品は「Sold」と表示される
     */
    public function purchasedItemDisplaysSold()
    {
            $item = Item::factory()->create(['status' => item::STATUS_SOLD]);

            $response = $this->get(route('items.index'));
            $response->assertStatus(200);

            // 売り切れ
            $response->assertSeeText($item->name);
            $response->assertSeeText('Sold');

    }

    /**
     * @test
     * 自分が出品した商品は表示されない
     */
    public function ownItemsAreNotDisplayed()
    {
        // ユーザーを作成してログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        // ログインしているユーザーを確認
        $this->assertAuthenticatedAs($user);

        foreach(Item::STATUSES as $status){
            // 商品作成
            $item = Item::factory()->create([
            'user_id' => $user->id,
            'status' => $status,
        ]);

            $response = $this->get(route('items.index'));
            $response->assertStatus(200);

            //  商品ステータスに関わらず表示されない
            $response->assertDontSeeText($item->name);

            // テストデータ削除
            Item::where('id', $item->id)->delete();
        }
    }
}