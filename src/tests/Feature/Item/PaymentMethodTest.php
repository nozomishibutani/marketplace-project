<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use App\Models\User;
use App\Models\Order;
use App\Models\Profile;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;
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
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // ハイフンありにする
        $postcode = substr($user->profile->postcode, 0, 3) . '-' . substr($user->profile->postcode, 3);

        // 商品を作成
        $item = $this->createItemWithCategory(Item::STATUS_ON_SALE);

        // 購入画面
        $response = $this->get(route('purchase.confirm', $item->id));
        $response->assertStatus(200);

        // お支払い方法をコンビニ払いに変更
        $response = $this->post(route('purchase.confirm', $item->id), [
            'payment_method' => Order::PAYMENT_CONVENIENCE,
            'postcode' => $postcode,
            'address' => $user->profile->address,
            'building' => $user->profile->building,
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
            'address' => $user->profile->address,
            'building' => $user->profile->building,
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
    protected function createItemWithCategory($status) {
        $item = Item::factory()->create([
            'status' => $status,
        ]);

        $category = Category::factory()->create();
        $item->categories()->attach($category->id);

        return $item;
    }
}
