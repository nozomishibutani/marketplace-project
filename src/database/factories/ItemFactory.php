<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Category;
use Faker\Generator as Faker;

class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Faker 日本語化
        $faker = \Faker\Factory::create('ja_JP');

        return [
        'user_id' => User::factory(),
        'name' =>  $faker->text(5),
        'brand_name' => $faker->text(10),
        'description' =>  $faker->sentence(),
        'price' => rand(100, 99999),
        'condition' => rand(1, 4),
        // 呼び出し元で値を指定する
        //1.出品中 2.売り切れ 3.出品停止
        'status' => null,
        'img' => $faker->word(8) .'.jpg',
        ];
    }
}
