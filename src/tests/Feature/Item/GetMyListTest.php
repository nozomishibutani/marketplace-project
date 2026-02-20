<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use App\Models\User;
use App\Models\Favorite;
use Tests\TestCase;
use App\Common\Common;

class GetMyListTest extends TestCase
{
    /**
     * マイリスト一覧取得
     *
     * @return void
     */
    public function testGetMyList()
    {
        // ------------------------
        // いいねした商品だけが表示される
        // 購入済み商品は「Sold」と表示される
        // ------------------------

        // ユーザーを作成してログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // いいねする商品を作成
        // [任意] 商品ステータス
        $favorites['activeItem'] = Item::factory()->create(['status' => 1]); // 出品中
        $favorites['soldItem']   = Item::factory()->create(['status' => 2]); // 売り切れ
        $favorites['suspendedItem'] = Item::factory()->create(['status' => 3]); // 出品停止中
        // いいねしない商品
        // [任意] 商品ステータス
        $unfavorites['activeItem'] = Item::factory()->create(['status' => 1]); // 出品中
        $unfavorites['soldItem']   = Item::factory()->create(['status' => 2]); // 売り切れ
        $unfavorites['suspendedItem'] = Item::factory()->create(['status' => 3]); // 出品停止中

        // いいねする
        foreach($favorites as $favorite){
            Favorite::factory()->create([
                'user_id' => $user->id,
                'item_id' => $favorite->id,
            ]);
        }

        // マイリストにアクセス
        $response = $this->get(route('items.index', ['tab' => Common::TAB_MYLIST]));
        $response->assertStatus(200);

        // ログインユーザーがいいねした商品を取得
        $activeFavorites = Item::whereHas('favorites', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
        })
        ->get('id')
        ->toArray();

        $content = $response->getContent();

        foreach ($activeFavorites as $activeFavorite) {
            $item = Item::find($activeFavorite['id']);
            switch ($item->status) {
                case '1':
                // 出品中
                $this->assertStringContainsString('data-item-id="'.$item->id.'"', $content);
                // [任意] 出品中の商品は Sold 表示されない
                $this->assertStringNotContainsString($item->name . 'Sold', $content);
                break;

                case '2':
                // 売り切れ
                $this->assertStringContainsString('data-item-id="'.$item->id.'"', $content);
                $response->assertSeeTextInOrder([$item->name,'Sold']);
                break;

                case '3':
                // [任意] 出品停止
                $this->assertStringNotContainsString('data-item-id="'.$item->id.'"', $content);
                break;
            }
        }

        // [任意] いいねしていない商品は表示されない
        foreach($unfavorites as $unfavorite){
            $this->assertStringNotContainsString($unfavorite->id, $content);
        }

        // ------------------------
        // 未認証の場合は何も表示されない
        // ------------------------
        // マイリストにアクセス
        auth()->logout();
        $response = $this->get(route('items.index', ['tab' => Common::TAB_MYLIST]));
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringNotContainsString('data-item-id=', $content);
    }
}
