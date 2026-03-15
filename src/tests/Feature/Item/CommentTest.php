<?php

namespace Tests\Feature\Item;

use Tests\TestCase;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use App\Models\Profile;
use App\Models\Comment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentTest extends TestCase
{
    /**
     * コメント送信機能
     */
    use RefreshDatabase;

    /**
     * @test
     * ログイン済みのユーザーはコメントを送信できる
     */
    public function loginUserCanPostComment() {
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // 商品を作成
        $item = $this->createItemWithCategory(Item::STATUS_ON_SALE);

        // コメント数を確認
        $beforeCount = Comment::count();
        // コメントする
        $response = $this->post(route('items.comment', $item->id), [
            'content' => 'テストコメント',
        ]);
        $response->assertRedirect(route('items.show', $item->id));

        // コメントが保存されているか
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'content' => 'テストコメント',
        ]);

        // コメント数が増えている
        $this->assertEquals($beforeCount + 1, Comment::count());
    }

    /**
     * @test
     * ログイン前のユーザーはコメントを送信できない
     */
    public function gestUserCannotPostComment() {
        // 未ログイン状態
        $this->assertGuest();

        // 商品を作成
        $item = $this->createItemWithCategory(Item::STATUS_SOLD);

        // コメント数を確認
        $this->assertDatabaseCount('comments', 0);
        // コメントする
        $response = $this->post(route('items.comment', $item->id), [
            'content' => 'テストコメント',
        ]);

        // ログイン画面リダイレクト
        $response->assertRedirect(route('login'));

        // コメントが保存されていない
        $this->assertDatabaseCount('comments', 0);
    }

    /**
     * @test
     * コメントが入力されていない場合、バリデーションメッセージが表示される
     */
    public function commentIsRequired() {
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // 商品を作成
        $item = $this->createItemWithCategory(Item::STATUS_SOLD);

        // コメントする
        $response = $this->post(route('items.comment', $item->id), [
            'content' => '',
        ]);

        $response->assertSessionHasErrors([
            'content' => 'コメントを入力してください',
        ]);
    }

    /**
     * @test
     * コメントが256字以上の場合、バリデーションメッセージが表示される
     *
     * テスト要件では255文字以上となっているが、仕様は最大255文字のため
     * 255文字を超える入力（256文字以上）でエラーになることを確認する
     */
    public function commentCannotExceed255Characters(){
        /** @var \App\Models\User $user */
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        // 商品を作成
        $item = $this->createItemWithCategory(Item::STATUS_SOLD);

        // コメントする
        $response = $this->post(route('items.comment', $item->id), [
            'content' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors([
            'content' => 'コメントは255文字以内で入力してください',
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
    protected function createItemWithCategory($status) {
        $item = Item::factory()->create([
            'status' => $status,
        ]);

        $category = Category::factory()->create();
        $item->categories()->attach($category->id);

        return $item;
    }
}
