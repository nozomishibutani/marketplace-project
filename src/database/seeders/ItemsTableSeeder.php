<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $params = [
            [
                'item' => [
                    'user_id' => 1,
                    'name' => '腕時計',
                    'brand_name' => 'Rolax',
                    'description' => 'スタイリッシュなデザインのメンズ腕時計',
                    'price' => 15000,
                    'condition' => Item::CONDITION_GOOD,
                    'status' => Item::STATUS_ON_SALE,
                    'img' => 'items/Armani+Mens+Clock.jpg',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                'categories' => [1],
            ],
            [
                'item' => [
                    'user_id' => 2,
                    'name' => 'HDD',
                    'brand_name' => '西芝',
                    'description' => '高速で信頼性の高いハードディスク',
                    'price' => 5000,
                    'condition' => Item::CONDITION_NO_DAMAGE,
                    'status' => Item::STATUS_ON_SALE,
                    'img' => 'items/HDD+Hard+Disk.jpg',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                'categories' => [2],
            ],
            [
                'item' => [
                    'user_id' => 3,
                    'name' => '玉ねぎ3束',
                    'brand_name' => 'なし',
                    'description' => '新鮮な玉ねぎ3束のセット',
                    'price' => 300,
                    'condition' => Item::CONDITION_SCRATCH,
                    'status' => Item::STATUS_ON_SALE,
                    'img' => 'items/iLoveIMG+d.jpg',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                'categories' => [10],
            ],
            [
                'item' => [
                    'user_id' => 3,
                    'name' => '革靴',
                    'brand_name' => 'なし',
                    'description' => 'クラシックなデザインの革靴',
                    'price' => 4000,
                    'condition' => Item::CONDITION_BAD,
                    'status' => Item::STATUS_ON_SALE,
                    'img' => 'items/Leather+Shoes+Product+Photo.jpg',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                'categories' => [5],
            ],
            [
                'item' => [
                    'user_id' => 4,
                    'name' => 'ノートPC',
                    'brand_name' => '',
                    'description' => '高性能なノートパソコン',
                    'price' => 45000,
                    'condition' => Item::CONDITION_GOOD,
                    'status' => Item::STATUS_ON_SALE,
                    'img' => 'items/Living+Room+Laptop.jpg',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                'categories' => [2],
            ],
            [
                'item' => [
                    'user_id' => 5,
                    'name' => 'マイク',
                    'brand_name' => 'なし',
                    'description' => '高品質のレコーディング用マイク',
                    'price' => 8000,
                    'condition' => Item::CONDITION_NO_DAMAGE,
                    'status' => Item::STATUS_ON_SALE,
                    'img' => 'items/Music+Mic+4632231.jpg',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                'categories' => [2],
            ],
            [
                'item' => [
                    'user_id' => 6,
                    'name' => 'ショルダーバッグ',
                    'brand_name' => '',
                    'description' => 'おしゃれなショルダーバッグ',
                    'price' => 3500,
                    'condition' => Item::CONDITION_SCRATCH,
                    'status' => Item::STATUS_ON_SALE,
                    'img' => 'items/Purse+fashion+pocket.jpg',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                'categories' => [4],
            ],
            [
                'item' => [
                    'user_id' => 7,
                    'name' => 'タンブラー',
                    'brand_name' => 'なし',
                    'description' => '使いやすいタンブラー',
                    'price' => 500,
                    'condition' => Item::CONDITION_BAD,
                    'status' => Item::STATUS_ON_SALE,
                    'img' => 'items/Tumbler+souvenir.jpg',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                'categories' => [11],
            ],
            [
                'item' => [
                    'user_id' => 3,
                    'name' => 'コーヒーミル',
                    'brand_name' => 'Starbacks',
                    'description' => '手動のコーヒーミル',
                    'price' => 4000,
                    'condition' => Item::CONDITION_GOOD,
                    'status' => Item::STATUS_ON_SALE,
                    'img' => 'items/Waitress+with+Coffee+Grinder.jpg',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                'categories' => [2],
            ],
            [
                'item' => [
                    'user_id' => 2,
                    'name' => 'メイクセット',
                    'brand_name' => '',
                    'description' => '便利なメイクアップセット',
                    'price' => 2500,
                    'condition' => Item::CONDITION_NO_DAMAGE,
                    'status' => Item::STATUS_ON_SALE,
                    'img' => 'items/外出メイクアップセット.jpg',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                'categories' => [6],
            ],
        ];

        foreach ($params as $param) {
            $item = Item::create($param['item']);
            $item->categories()->attach($param['categories']);
        }
    }
}
