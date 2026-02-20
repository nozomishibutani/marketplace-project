<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use Tests\TestCase;

class SearchItemTest extends TestCase
{
    /**
     * 商品検索機能
     *
     * @return void
     */
    public function testSearchItem()
    {
        // ------------------------
        // 「商品名」で部分一致検索ができる
        // ------------------------

        // 商品を作成
        $targetItem = Item::factory()->create([
            'name' => 'Banana Watch'
        ]);
        $otherItem = Item::factory()->create([
            'name' => 'Apple Monitor',
        ]);

        // 検索実行
        $response = $this->get(route('search', ['keyword' => 'Banana']));
        $response->assertStatus(200);

        // 検索結果に含まれる
        $response->assertSeeText($targetItem->name);

        // 含まれないことを確認
        $response->assertDontSeeText($otherItem->name);
        }

        // ------------------------
        // 検索状態がマイリストでも保持されている
        // ------------------------

        // 保留
    }
