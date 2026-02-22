<?php

namespace Tests\Feature\Item;


use App\Models\Item;
use App\Models\User;
use App\Models\Order;
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
        // ユーザーを作成してログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        // ログインしているユーザーを確認
        $this->assertAuthenticatedAs($user);
        // 商品を作成
        $item = Item::factory()->create(['status' => Item::STATUS_ON_SALE,]);
        // カテゴリを作成
        $category = Category::factory()->create();
        $item->categories()->attach($category->pluck('id'));
        // 購入画面に遷移
        $response = $this->get(route('purchase.confirm', $item->id));
        $response->assertStatus(200);

        // プロフィールがないから配送先住所の確認ができない
        // プロフィール作成後に再度確認する　→　他のユーザー作成の時も同時にプロフィールが作成されるよに修正必須

        // 購入
        $response = $this->post(route('store', $item->id),[
            'item_id' => $item->id,
            'payment_method' => order::PAYMENT_CONVENIENCE,
            'status' => 1, // 要確認
            'postcode' => '1234567',
            'address' => 'テスト住所',
            'building' => 'テスト建物名',
        ]);

        $response->assertRedirect(route('items.index'));

        // DBに値が登録されているか
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => order::PAYMENT_CONVENIENCE,
            'status' => 1, // 要確認
            'postcode' => '1234567',
            'address' => 'テスト住所',
            'building' => 'テスト建物名',
        ]);
        // 商品ステータスが売り切れになっているか
        $this->assertDatabaseHas('items', [
                'id' => $item->id,
                'status' => Item::STATUS_SOLD,
            ]);
    }
}
