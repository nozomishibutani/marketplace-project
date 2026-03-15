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
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // 商品を作成
        $item = $this->createItemWithCategory(Item::STATUS_ON_SALE, 1000);

        // 購入画面
        $response = $this->get(route('purchase.confirm', $item->id));
        $response->assertStatus(200);

        // 住所変更
        $response = $this->post(route('purchase.update', $item->id),[
                    'postcode' => '123-1234',
                    'address' => '変更先住所',
                    'building' => '変更先建物名',
                ]);
        $response->assertRedirect(route('purchase.confirm', $item->id));

        // 購入画面にリダイレクト
        $response = $this->followRedirects($response);
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
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // 商品を作成
        $item = $this->createItemWithCategory(Item::STATUS_ON_SALE, 1000);

        // 購入画面
        $response = $this->get(route('purchase.confirm', $item->id));
        $response->assertStatus(200);

        // 住所変更
        $response = $this->post(route('purchase.update', $item->id),[
            'postcode' => '987-9876',
            'address' => '変更先住所',
            'building' => '変更先建物名',
        ]);
        $response->assertRedirect(route('purchase.confirm', $item->id));

        // 変更をセッションで保持している
        $response->assertSessionHasInput([
            'postcode' => '987-9876',
            'address' => '変更先住所',
            'building' => '変更先建物名',
        ]);
        $postcode =session()->getOldInput('postcode');
        $address = session()->getOldInput('address');
        $building = session()->getOldInput('building');

        // セッションの内容で購入
        $response = $this->post(route('purchase.store', $item->id), [
            'payment_method' => Order::PAYMENT_CONVENIENCE,
            'postcode' => $postcode,
            'address' => $address,
            'building' => $building,
        ]);

        // セッションの内容がDBに登録されている
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
