<?php

namespace Tests\Feature\Item;

use Tests\TestCase;
use App\Models\Item;
use App\Models\User;


class GetItemListTest extends TestCase
{
    /**
     * 商品一覧取得
     *
     * @return void
     */
    public function testGetItemList()
    {
        // ------------------------
        // 全商品を取得できる
        // 購入済み商品は「Sold」と表示される
        // ------------------------

        // 商品を作成する
        $activeItem = Item::factory()->create(['status' => 1]); // 出品中
        $soldItem   = Item::factory()->create(['status' => 2]); // 売り切れ
        $suspendedItem = Item::factory()->create(['status' => 3]); // 出品停止中

        $response = $this->get(route('items.index'));
        $response->assertStatus(200);

        $content = $response->getContent();

       // 出品中
        $this->assertStringContainsString('data-item-id="'.$activeItem->id.'"', $content);
        // [任意] 出品中の商品は Sold 表示されない
        $this->assertStringNotContainsString($activeItem->name . 'Sold', $content);

        // 売り切れ
        $this->assertStringContainsString('data-item-id="'.$soldItem->id.'"', $content);
        $response->assertSeeTextInOrder([$soldItem->name,'Sold']);

        // [任意] 出品停止
        $this->assertStringNotContainsString('data-item-id="'.$suspendedItem->id.'"', $content);

        // ------------------------
        // 自分が出品した商品は表示されない
        // ------------------------

        // ユーザーを作成してログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 自分の商品をステータスごとに作成
        $ownActive     = Item::factory()->create(['user_id' => $user->id, 'status' => 1]); // 出品中
        $ownSold       = Item::factory()->create(['user_id' => $user->id, 'status' => 2]); // 売り切れ
        $ownSuspended  = Item::factory()->create(['user_id' => $user->id, 'status' => 3]); // 出品停止中

        // [任意] 他のユーザーの商品も作成
        $otherUser = User::factory()->create();
        $otherItemActive = Item::factory()->create(['user_id' => $otherUser->id, 'status' => 1]);
        $otherItemSold = Item::factory()->create(['user_id' => $otherUser->id, 'status' => 2]);
        $otherItemSuspended = Item::factory()->create(['user_id' => $otherUser->id, 'status' => 3]);

        // 商品一覧画面にアクセス
        $response = $this->get(route('items.index'));
        $response->assertStatus(200);

        $content = $response->getContent();

        // 自分の商品は表示されない
        $this->assertStringNotContainsString('data-item-id="'.$ownActive->id.'"', $content);
        $this->assertStringNotContainsString('data-item-id="'.$ownSold->id.'"', $content);
        $this->assertStringNotContainsString('data-item-id="'.$ownSuspended->id.'"', $content);

        // [任意] 他のユーザーの商品は表示される
        $this->assertStringContainsString('data-item-id="'.$otherItemActive->id.'"', $content);
        $this->assertStringContainsString('data-item-id="'.$otherItemSold->id.'"', $content);
        $this->assertStringNotContainsString('data-item-id="'.$otherItemSuspended->id.'"', $content);
    }
}