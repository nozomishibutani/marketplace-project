<?php

namespace Tests\Feature\Item;


use App\Models\Item;
use App\Models\User;
use App\Models\Order;
use App\Models\Profile;
use App\Models\Category;
use App\Common\Common;
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

        // 購入
        $response = $this->post(route('purchase.store', $item->id),[
            'payment_method' => order::PAYMENT_CONVENIENCE,
            'status' => 1, // 要確認
            'postcode' => $postcode, // ハイフンあり
            'address' => $profile->address,
            'building' => $profile->building,
        ]);

        $response->assertRedirect(route('items.index'));

        // DBに値が登録されているか
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => order::PAYMENT_CONVENIENCE,
            'status' => 1, // 要確認
            'postcode' => $profile->postcode, // ハイフンなし
            'address' => $profile->address,
            'building' => $profile->building,
        ]);
        // 商品ステータスが売り切れになっているか
        $this->assertDatabaseHas('items', [
                'id' => $item->id,
                'status' => Item::STATUS_SOLD,
        ]);
    }

    /**
     * @test
     * 「購入した商品は商品一覧画面にて「sold」と表示される
     */
    public function purchasedItemDisplaysSold()
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

        // 購入
        $response = $this->post(route('purchase.store', $item->id), [
            'payment_method' => order::PAYMENT_CONVENIENCE,
            'status' => 1, // 要確認
            'postcode' => $postcode, // ハイフンあり
            'address' => $profile->address,
            'building' => $profile->building,
        ]);

        $response = $this->get(route('items.index'));

        //売り切れ
        $response->assertSeeText($item->name);
        $response->assertSeeText('Sold');
    }

    /**
     * @test
     * 「プロフィール/購入した商品一覧」に追加されている
     */
    public function purchasedItemIsAddedToProfilePurchaseList()
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

        // 購入
        $response = $this->post(route('purchase.store', $item->id), [
            'payment_method' => order::PAYMENT_CONVENIENCE,
            'status' => 1, // 要確認
            'postcode' => $postcode, // ハイフンあり
            'address' => $profile->address,
            'building' => $profile->building,
        ]);

        // 購入した商品に遷移
        $response = $this->get(route('profile.index', ['page' => Common::PAGE_BUY]));
        $response->assertStatus(200);

        $response->assertSeeText($item->name);
    }
}
