<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use Tests\TestCase;
use App\Models\Category;
use App\Common\Common;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;
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
    public function canSearchItemByPartialName() {
        // 商品を作成
        $item = $this->createItemWithCategory(Item::STATUS_SOLD,'Banana Watch');

         // 1. 検索欄にキーワードを入力
         // 2. 検索ボタンを押す
        $response = $this->get(route('search', ['keyword' => 'Banana']));
        $response->assertStatus(200);

        // 部分一致する商品が表示される
        $response->assertSeeText($item->name);
    }

    /**
     * @test
     * 検索状態がマイリストでも保持されている
     */
    public function keepKeyWordOnFormOfMyList() {
        // 商品を作成
        $item = $this->createItemWithCategory(Item::STATUS_SOLD,'Apple Mirror');

        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // いいね状態を作る
        $user->favorites()->attach($item->id);

        // 1. ホームページで商品を検索
        $response = $this->get(route('items.index'));
        $response->assertStatus(200);
        $response = $this->get(route('search', ['keyword' => 'Apple']));
        $response->assertStatus(200);

        // 2. 検索結果が表示される
        $response->assertSeeText($item->name);

        // 3. マイリストページに遷移
        $response = $this->get(route('items.index', ['tab' => Common::TAB_MYLIST, 'keyword' => 'Apple']));
        $response->assertStatus(200);

        // 検索キーワードが保持されている
        $response->assertSee('value="Apple"', false);

        // 検索状態がマイリストでも保持されている
        $response->assertSeeText($item->name);
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
    protected function createItemWithCategory($status, $name) {
        $item = Item::factory()->create([
            'status' => $status,
            'name' => $name,
        ]);

        $category = Category::factory()->create();
        $item->categories()->attach($category->id);

        return $item;
    }
}
