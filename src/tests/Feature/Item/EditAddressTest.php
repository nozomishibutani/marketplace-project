<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use App\Models\User;
use App\Models\Order;
use App\Models\Profile;
use App\Models\Category;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;


class EditAddressTest extends TestCase
{
    /**
     * 配送先変更機能
     *
     */
    use RefreshDatabase;

    /**
     * @test
     * 「送付先住所変更画面にて登録した住所が商品購入画面に反映されている
     */
    public function updatedShippingAddressIsReflectedOnPurchasePage()
    {
        // ユーザーを作成してログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        // プロフィール登録
        Profile::factory()->create(['user_id' => $user->id]);
        // ログインしているユーザーを確認
        $this->assertAuthenticatedAs($user);
        // 商品を作成
        $item = Item::factory()->create(['status' => Item::STATUS_ON_SALE,]);
        // カテゴリを作成
        $category = Category::factory()->create();
        $item->categories()->attach($category->pluck('id'));

        // 住所変更
        $response = $this->post(route('purchase.update', $item->id),[
                    'postcode' => '123-1234',
                    'address' => '変更先住所',
                    'building' => '変更先建物名',
                ]);

        // 購入画面に遷移
        $response->assertStatus(200);
        $response->assertSee('postcode','123-1234', false);
        $response->assertSee('address','変更先住所', false);
        $response->assertSee('building','変更先建物名', false);
    }

    /**
     * @test
     * 購入した商品に送付先住所が紐づいて登録される
     */
    public function addressIsStoredWithPurchasedItem()
    {
        // ユーザーを作成してログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        // プロフィール登録
        Profile::factory()->create(['user_id' => $user->id]);
        // ログインしているユーザーを確認
        $this->assertAuthenticatedAs($user);
        // 商品を作成
        $item = Item::factory()->create(['status' => Item::STATUS_ON_SALE,]);
        // カテゴリを作成
        $category = Category::factory()->create();
        $item->categories()->attach($category->pluck('id'));

        // 住所変更
        $postcode ='987-9876';
        $address = '変更先住所';
        $building = '変更先建物名';
        $this->post(route('purchase.update', $item->id),[
            'postcode' => $postcode,
            'address' => $address,
            'building' => $building,
        ]);

        // 購入
        $this->post(route('purchase.store', $item->id), [
            'payment_method' => Order::PAYMENT_CONVENIENCE,
            'postcode' => $postcode,
            'address' => $address,
            'building' => $building,
        ]);

        $this->assertDatabaseHas('orders', [
            'postcode' => str_replace('-', '', $postcode),
            'address' => $address,
            'building' => $building,
        ]);

    }
}
