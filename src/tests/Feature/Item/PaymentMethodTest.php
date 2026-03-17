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
    public function canPurchaseItem() {
        // 商品を作成
        $item = $this->createItemWithCategory(Item::STATUS_ON_SALE);

        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // 1. 支払い方法選択画面を開く
        $response = $this->get(route('purchase.confirm', $item->id));
        $response->assertStatus(200);

        // 2. プルダウンメニューから支払い方法を選択する(コンビニ)
        $response = $this->post(route('purchase.confirm', $item->id), [
            'payment_method' => Order::PAYMENT_CONVENIENCE,
            'postcode' => substr($user->profile->postcode, 0, 3) . '-' . substr($user->profile->postcode, 3),
            'address' => $user->profile->address,
            'building' => $user->profile->building,
        ]);

        // 選択した支払い方法が正しく反映される
        $response->assertStatus(200);
        $response->assertSeeText(Order::PAYMENT_METHODS[Order::PAYMENT_CONVENIENCE]);
        $response->assertSeeInOrder(['value="' . Order::PAYMENT_CONVENIENCE . '"','selected',], false);

        // 2. プルダウンメニューから支払い方法を選択する(カード)
        $response = $this->post(route('purchase.confirm', $item->id), [
            'payment_method' => Order::PAYMENT_CARD,
            'postcode' => substr($user->profile->postcode, 0, 3) . '-' . substr($user->profile->postcode, 3),
            'address' => $user->profile->address,
            'building' => $user->profile->building,
        ]);

        // 選択した支払い方法が正しく反映される
        $response->assertStatus(200);
        $response->assertSeeText(Order::PAYMENT_METHODS[Order::PAYMENT_CARD]);
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
