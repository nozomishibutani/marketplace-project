<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use App\Models\Profile;
use App\Models\Order;
use Tests\TestCase;
use App\Common\Common;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileTest extends TestCase
{
    /**
     * ユーザー情報取得
     * ユーザー情報変更
     */
    use RefreshDatabase;

    /**
     * @test
     * 必要な情報が取得できる（プロフィール画像、ユーザー名、出品した商品一覧、購入した商品一覧）
     */
    public function userDetailIsDisplayed() {
        // フェイクのストレージを作成
        Storage::fake('public');
        // 画像を作成し保存
        $avatar = UploadedFile::fake()->image('avatar_dummy.png');
        $sellItemImg = UploadedFile::fake()->image('sell_dummy.png');
        $buyItemImg = UploadedFile::fake()->image('buyItem_dummy.png');
        $avatarPath = $avatar->store('profiles', 'public');
        $sellItemImgPath = $sellItemImg->store('items', 'public');
        $buyItemImgPath = $buyItemImg->store('items', 'public');

        // 1. ユーザーにログインする
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser($avatarPath);
        $this->actingAs($user);

        // 出品商品を作成
        $otherUser = User::factory()->create();
        $sellItem = $this->createItemWithCategory(Item::STATUS_ON_SALE, $user->id, $sellItemImgPath);

        // 購入商品を作成
        $buyItem = $this->createItemWithCategory(Item::STATUS_ON_SALE, $otherUser->id, $buyItemImgPath);
        Order::factory()->create([
            'user_id' => $user->id,
            'item_id' => $buyItem->id,
            'payment_method' => Order::PAYMENT_CARD
        ]);

        // 2. プロフィールページを開く
        $response = $this->get(route('profile.index'));
        $response->assertStatus(200);

        // プロフィール画像、ユーザー名、出品した商品一覧、購入した商品一覧が正しく表示される
        // プロフィール画像
        $response->assertSee($user->profile->avatar, false);
        // ユーザー名
        $response->assertSeeText($user->username);

        // 出品
        $response = $this->get(route('profile.index', ['page' => Common::PAGE_SELL]));
        $response->assertStatus(200);
        $response->assertSee($user->profile->avatar, false);
        $response->assertSeeText($user->username);
        $response->assertSeeText($sellItem->name);
        $response->assertSee($sellItem->img, false);

        // 購入
        $response = $this->get(route('profile.index', ['page' => Common::PAGE_BUY]));
        $response->assertStatus(200);
        $response->assertSee($user->profile->avatar, false);
        $response->assertSeeText($user->username);
        $response->assertSeeText($buyItem->name);
        $response->assertSee($buyItem->img, false);
    }

    /**
     * @test
     * 変更項目が初期値として過去設定されていること（プロフィール画像、ユーザー名、郵便番号、住所）
     */
    public function editProfilePageHasDefaultValues()
    {
        // フェイクのストレージを作成
        Storage::fake('public');
        // 画像を作成し保存
        $avatar = UploadedFile::fake()->image('avatar_dummy.png');
        $avatarPath = $avatar->store('profiles', 'public');

        // 1. ユーザーにログインする
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser($avatarPath);
        $this->actingAs($user);

        // 2. プロフィールページを開く
        $response = $this->get(route('profile.edit'));
        $response->assertStatus(200);

        // 各項目の初期値が正しく表示されている
        $response->assertSee($user->profile->avatar, false);
        $response->assertSee($user->username, false);
        $response->assertSee($user->profile->postcode, false);
        $response->assertSee($user->profile->address, false);
        $response->assertSee($user->profile->building, false);
    }

    /**
     * @test
     * ユーザー作成
     */
    protected function createVerifiedUser($avatarPath) {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);
        $user->markEmailAsVerified();
        Profile::factory()->create([
            'user_id' => $user->id,
            'avatar' => $avatarPath,
            ]);
        return $user;
    }

    /**
     * @test
     * 商品作成
     */
    protected function createItemWithCategory($status, $userId, $path) {
        $item = Item::factory()->create([
            'status' => $status,
            'user_id' => $userId,
            'img' => $path,
        ]);

        $category = Category::factory()->create();
        $item->categories()->attach($category->id);

        return $item;
    }
}
