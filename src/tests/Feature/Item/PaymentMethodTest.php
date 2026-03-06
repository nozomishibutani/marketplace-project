<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use App\Models\User;
use App\Models\Order;
use App\Models\Profile;
use App\Models\Category;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentMethodTest extends TestCase
{
    /**
     * 支払い方法選択機能
     *
     */
    use RefreshDatabase;

    /**
     * @test
     * 小計画面で変更が反映される
     */
    public function canPurchaseItem()
    {
        // ユーザーを作成してログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        // プロフィール登録
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        // ハイフンあり郵便番号にする
        $postcode = substr($profile->postcode, 0, 3) . '-' . substr($profile->postcode, 3);
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


        // お支払い方法をコンビニ払いに変更
        $response = $this->post(route('purchase.confirm', $item->id), [
            'payment_method' => Order::PAYMENT_CONVENIENCE,
            'postcode' => $postcode,
            'address' => $profile->address,
            'building' => $profile->building,
        ]);

        // 支払方法欄で表示されているか
        // セレクトボックス内の値と支払方法欄の表示で合計2回
        $response->assertStatus(200);
        $this->assertEquals(
            2,
            substr_count(
                $response->getContent(),
                Order::PAYMENT_METHODS[Order::PAYMENT_CONVENIENCE]
            )
        );

        // セレクトボックスで選択されているか
        $response->assertSeeInOrder(['value="' . Order::PAYMENT_CONVENIENCE . '"','selected',], false);

        // お支払方法をカードに変更
        $response = $this->post(route('purchase.confirm', $item->id), [
            'payment_method' => Order::PAYMENT_CARD,
            'postcode' => $postcode,
            'address' => $profile->address,
            'building' => $profile->building,
        ]);

        // 支払方法欄で表示されているか
        // セレクトボックス内の値と支払方法欄の表示で合計2回
        $response->assertStatus(200);
        $this->assertEquals(
            2,
            substr_count(
                $response->getContent(),
                Order::PAYMENT_METHODS[Order::PAYMENT_CARD]
            )
        );

        // セレクトボックスで選択されているか
        $response->assertSeeInOrder(['value="' . Order::PAYMENT_CARD . '"','selected',], false);

    }
}
