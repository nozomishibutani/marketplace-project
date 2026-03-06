<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use Tests\TestCase;
use App\Models\Category;
use App\Common\Common;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchItemTest extends TestCase
{
    /**
     * 商品検索機能
     */
    use RefreshDatabase;

    /**
     * @test
     * 「商品名」で部分一致検索ができる
     */
    public function canSearchItemByPartialName()
    {
        // 商品を作成
        $item = Item::factory()->create([
            'status' => Item::STATUS_ON_SALE,
            'name' => 'Banana Watch'
        ]);
        // カテゴリを作成
        $category = Category::factory()->create();
        $item->categories()->attach($category->pluck('id'));

        // 部分一致検索
        $response = $this->get(route('search', ['keyword' => 'Banana']));
        $response->assertStatus(200);

        // 検索結果に含まれる
        $response->assertSeeText($item->name);
        }

    /**
     * @test
     * 検索状態がマイリストでも保持されている
     */
    public function keepKeyWordOnFormOfMyList()
    {
        // 商品を作成
        $item = Item::factory()->create([
            'status' => Item::STATUS_SOLD,
            'name' => 'Apple Mirror'
        ]);
        // カテゴリを作成
        $category = Category::factory()->create();
        $item->categories()->attach($category->pluck('id'));

        // 部分一致検索
        $response = $this->get(route('search', ['keyword' => 'Apple']));
        $response->assertStatus(200);

        // マイリストにアクセス
        $response = $this->get(route('items.index', ['tab' => Common::TAB_MYLIST, 'keyword' => 'Apple']));
        $response->assertStatus(200);

        // 検索フォームに検索キーワードが保持されている
        $response->assertSee('value="Apple"', false);
        }
    }
