<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'name' => 'ファッション',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('categories')->insert($param);

        $param = [
            'name' => '家電',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('categories')->insert($param);

        $param = [
            'name' => 'インテリア',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('categories')->insert($param);

        $param = [
            'name' => 'レディース',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('categories')->insert($param);

        $param = [
            'name' => 'メンズ',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('categories')->insert($param);

        $param = [
            'name' => 'コスメ',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('categories')->insert($param);

        $param = [
            'name' => '本',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('categories')->insert($param);

        $param = [
            'name' => 'ゲーム',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('categories')->insert($param);

        $param = [
            'name' => 'スポーツ',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('categories')->insert($param);

        $param = [
            'name' => 'キッチン',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('categories')->insert($param);

        $param = [
            'name' => 'ハンドメイド',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('categories')->insert($param);

        $param = [
            'name' => 'アクセサリー',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('categories')->insert($param);

        $param = [
            'name' => 'おもちゃ',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('categories')->insert($param);

        $param = [
            'name' => 'ベビー・キッズ',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('categories')->insert($param);


    }
}
