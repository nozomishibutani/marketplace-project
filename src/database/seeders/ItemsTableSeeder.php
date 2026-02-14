<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'user_id' => 1,
            'category_id' => 1,
            'name' => '腕時計',
            'brand_name' => 'Rolax',
            'description' => 'スタイリッシュなデザインのメンズ腕時計',
            'price' => 15000,
            'condition' => 1,
            'status' => 1,
            'img' => 'img1',
            'created_at' => now(),
            'updated_at' => now(),

        ];
        DB::table('items')->insert($param);

        $param = [
            'user_id' => 2,
            'category_id' => 2,
            'name' => 'HDD',
            'brand_name' => '西芝',
            'description' => '高速で信頼性の高いハードディスク',
            'price' => 5000,
            'condition' => 2,
            'status' => 1,
            'img' => 'img2',
            'created_at' => now(),
            'updated_at' => now(),

        ];
        DB::table('items')->insert($param);

        $param = [
            'user_id' => 3,
            'category_id' => 10,
            'name' => '玉ねぎ3束',
            'brand_name' => 'なし',
            'description' => '新鮮な玉ねぎ3束のセット',
            'price' => 300,
            'condition' => 3,
            'status' => 1,
            'img' => 'img3',
            'created_at' => now(),
            'updated_at' => now(),

        ];
        DB::table('items')->insert($param);

        $param = [
            'user_id' => 4,
            'category_id' => 2,
            'name' => 'ノートPC',
            'brand_name' => '',
            'description' => '高性能なノートパソコン',
            'price' => 45000,
            'condition' => 1,
            'status' => 1,
            'img' => 'img4',
            'created_at' => now(),
            'updated_at' => now(),

        ];
        DB::table('items')->insert($param);

        $param = [
            'user_id' => 5,
            'category_id' => 2,
            'name' => 'マイク',
            'brand_name' => 'なし',
            'description' => '高品質のレコーディング用マイク',
            'price' => 8000,
            'condition' => 2,
            'status' => 1,
            'img' => 'img5',
            'created_at' => now(),
            'updated_at' => now(),

        ];
        DB::table('items')->insert($param);

        $param = [
            'user_id' => 6,
            'category_id' => 4,
            'name' => 'ショルダーバッグ',
            'brand_name' => '',
            'description' => 'おしゃれなショルダーバッグ',
            'price' => 3500,
            'condition' => 3,
            'status' => 1,
            'img' => 'img6',
            'created_at' => now(),
            'updated_at' => now(),

        ];
        DB::table('items')->insert($param);

        $param = [
            'user_id' => 7,
            'category_id' => 11,
            'name' => 'タンブラー',
            'brand_name' => 'なし',
            'description' => '使いやすいタンブラー',
            'price' => 500,
            'condition' => 4,
            'status' => 1,
            'img' => 'img7',
            'created_at' => now(),
            'updated_at' => now(),

        ];
        DB::table('items')->insert($param);

        $param = [
            'user_id' => 3,
            'category_id' => 2,
            'name' => 'コーヒーミル',
            'brand_name' => 'Starbacks',
            'description' => '手動のコーヒーミル',
            'price' => 4000,
            'condition' => 1,
            'status' => 1,
            'img' => 'img8',
            'created_at' => now(),
            'updated_at' => now(),

        ];
        DB::table('items')->insert($param);

        $param = [
            'user_id' => 2,
            'category_id' => 6,
            'name' => 'メイクセット',
            'brand_name' => '',
            'description' => '便利なメイクアップセット',
            'price' => 2500,
            'condition' => 2,
            'status' => 1,
            'img' => 'img9',
            'created_at' => now(),
            'updated_at' => now(),

        ];
        DB::table('items')->insert($param);
    }
}
