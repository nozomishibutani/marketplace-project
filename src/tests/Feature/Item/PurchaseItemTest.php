<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use App\Models\User;
use App\Models\Order;
use App\Models\Profile;
use App\Models\Category;
use App\Common\Common;
use Illuminate\Support\Facades\Hash;
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
        // 商品作成
        $item = $this->createItemWithCategory(Item::STATUS_ON_SALE, 120);

        // 1. ユーザーにログインする
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // 2. 商品購入画面を開く
        $response = $this->get(route('purchase.confirm', $item->id));
        $response->assertStatus(200);

        // 3. 商品を選択して「購入する」ボタンを押下
        $response = $this->post(route('purchase.store', $item->id), [
            'payment_method' => Order::PAYMENT_CONVENIENCE,
            'postcode' => substr($user->profile->postcode, 0, 3) . '-' . substr($user->profile->postcode, 3),
            'address' => $user->profile->address,
            'building' => $user->profile->building,
        ]);

        // 購入が完了する
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => Order::PAYMENT_CONVENIENCE,
            'postcode' => $user->profile->postcode,
            'address' => $user->profile->address,
            'building' => $user->profile->building,
        ]);
        $this->assertDatabaseHas('items', [
                'id' => $item->id,
                'status' => Item::STATUS_SOLD,
        ]);
    }

    /**
     * @test
     * 「購入した商品は商品一覧画面にて「sold」と表示される
     */
    public function purchasedItemDisplaysSold() {
        // 商品作成
        $item = $this->createItemWithCategory(Item::STATUS_ON_SALE, 300000);

        // 1. ユーザーにログインする
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // 2. 商品購入画面を開く
        $response = $this->get(route('purchase.confirm', $item->id));
        $response->assertStatus(200);

        // 3. 商品を選択して「購入する」ボタンを押下
        $response = $this->post(route('purchase.store', $item->id), [
            'payment_method' => Order::PAYMENT_CONVENIENCE,
            'postcode' => substr($user->profile->postcode, 0, 3) . '-' . substr($user->profile->postcode, 3),
            'address' => $user->profile->address,
            'building' => $user->profile->building,
        ]);

        // 4. 商品一覧画面を表示する
        $response = $this->get(route('items.index'));

        // 購入した商品が「sold」として表示されている
        $response->assertSeeText($item->name);
        $response->assertSeeText('Sold');
    }

    /**
     * @test
     * 「プロフィール/購入した商品一覧」に追加されている
     */
    public function purchasedItemIsAddedToProfilePurchaseList() {
        /** @var \App\Models\User $user */
        // 1. ユーザーにログインする
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // 商品作成
        $item = $this->createItemWithCategory(Item::STATUS_ON_SALE, 3510);

        // 2. 商品購入画面を開く
        $response = $this->get(route('purchase.confirm', $item->id));
        $response->assertStatus(200);

        // 3. 商品を選択して「購入する」ボタンを押下
        $response = $this->post(route('purchase.store', $item->id), [
            'payment_method' => Order::PAYMENT_CONVENIENCE,
            'postcode' => substr($user->profile->postcode, 0, 3) . '-' . substr($user->profile->postcode, 3),
            'address' => $user->profile->address,
            'building' => $user->profile->building,
        ]);

        // 4. プロフィール画面を表示する
        $response = $this->get(route('profile.index', ['page' => Common::PAGE_BUY]));
        $response->assertStatus(200);

        // 購入した商品がプロフィールの購入した商品一覧に追加されている
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
    protected function createItemWithCategory($status, $price) {
        $item = Item::factory()->create([
            'status' => $status,
            'price' => $price,
        ]);

        $category = Category::factory()->create();
        $item->categories()->attach($category->id);

        return $item;
    }
}
