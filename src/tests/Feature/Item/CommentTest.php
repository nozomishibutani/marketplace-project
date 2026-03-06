<?php

namespace Tests\Feature\Item;


use Tests\TestCase;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use App\Models\Comment;

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
    public function loginUserCanPostComment(){

        // ユーザーを作成してログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        // ログインしているユーザーを確認
        $this->assertAuthenticatedAs($user);
        // 商品を作成
        $item = Item::factory()->create(['status' => Item::STATUS_ON_SALE]);
        // カテゴリを作成
        $category = Category::factory()->create();
        $item->categories()->attach($category->pluck('id'));
        // コメント数を確認
        $beforeCount = Comment::count();
        // コメントする
        $response = $this->post(route('items.comment', $item->id), [
            'content' => 'テストコメント',
        ]);
        $response->assertRedirect(route('items.show', $item->id));

        // DBにコメントが保存されているか
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
    public function gestUserCannotPostComment(){

        // 未ログイン状態
        $this->assertGuest();
        // 商品を作成
        $item = Item::factory()->create(['status' => Item::STATUS_SOLD]);
        // カテゴリを作成
        $category = Category::factory()->create();
        $item->categories()->attach($category->pluck('id'));
        // コメント数を確認
        $this->assertDatabaseCount('comments', 0);
        // コメントする
        $response = $this->post(route('items.comment', $item->id), [
            'content' => 'テストコメント',
        ]);

        // ログイン画面にリダイレクトされるか
        $response->assertRedirect(route('login'));

        // コメントが保存されていないか
        $this->assertDatabaseCount('comments', 0);
    }

    /**
     * @test
     * コメントが入力されていない場合、バリデーションメッセージが表示される
     */
    public function commentIsRequired(){

        // ユーザーを作成してログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        // ログインしているユーザーを確認
        $this->assertAuthenticatedAs($user);
        // 商品を作成
        $item = Item::factory()->create(['status' => Item::STATUS_ON_SALE]);
        // カテゴリを作成
        $category = Category::factory()->create();
        $item->categories()->attach($category->pluck('id'));
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
     */
    public function commentCannotExceed255Characters(){

        // ユーザーを作成してログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        // ログインしているユーザーを確認
        $this->assertAuthenticatedAs($user);
        // 商品を作成
        $item = Item::factory()->create(['status' => Item::STATUS_SOLD]);
        // カテゴリを作成
        $category = Category::factory()->create();
        $item->categories()->attach($category->pluck('id'));
        // コメントする
        $response = $this->post(route('items.comment', $item->id), [
            'content' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors([
            'content' => 'コメントは255文字以内で入力してください',
        ]);
    }
}
