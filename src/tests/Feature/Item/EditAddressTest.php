<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use App\Models\User;
use App\Models\Order;
use App\Models\Profile;
use App\Models\Category;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
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
        // 商品を作成
        $item = $this->createItemWithCategory(Item::STATUS_ON_SALE, 1000);

        // 1. ユーザーにログインする
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // 2. 送付先住所変更画面で住所を登録する
        $response = $this->post(route('purchase.update', $item->id),[
                    'postcode' => '123-1234',
                    'address' => 'テスト変更先住所',
                    'building' => 'テスト変更先建物名',
                ]);

        // 3. 商品購入画面を再度開く
        $response = $this->followRedirects($response);
        $response->assertSee('postcode','123-1234', false);
        $response->assertSee('address','テスト変更先住所', false);
        $response->assertSee('building','テスト変更先建物名', false);
    }

    /**
     * @test
     * 購入した商品に送付先住所が紐づいて登録される
     */
    public function addressIsStoredWithPurchasedItem()
    {
        // 商品を作成
        $item = $this->createItemWithCategory(Item::STATUS_ON_SALE, 1000);

        // 1. ユーザーにログインする
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // 2. 送付先住所変更画面で住所を登録する
        $response = $this->post(route('purchase.update', $item->id),[
            'postcode' => '987-9876',
            'address' => 'テスト変更先住所',
            'building' => 'テスト変更先建物名',
        ]);
        $response->assertRedirect(route('purchase.confirm', $item->id));

        // 変更をセッションで保持している
        $response->assertSessionHasInput([
            'postcode' => '987-9876',
            'address' => 'テスト変更先住所',
            'building' => 'テスト変更先建物名',
        ]);
        $postcode =session()->getOldInput('postcode');
        $address = session()->getOldInput('address');
        $building = session()->getOldInput('building');

        // 3. 商品を購入する
        $response = $this->post(route('purchase.store', $item->id), [
            'payment_method' => Order::PAYMENT_CONVENIENCE,
            'postcode' => $postcode,
            'address' => $address,
            'building' => $building,
        ]);

        // 正しく送付先住所が紐づいている
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'postcode' => str_replace('-', '', $postcode),
            'address' => $address,
            'building' => $building,
        ]);
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
