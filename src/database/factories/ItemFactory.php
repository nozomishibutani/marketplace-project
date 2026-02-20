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
        'category_id' => Category::factory(),
        'name' =>  $faker->text(5) . rand(1, 100),
        'brand_name' => $faker->text(10) . rand(1, 100),
        'description' =>  $faker->sentence(),
        'price' => rand(100, 99999),
        'condition' => rand(1, 4),
        'status' => null,
        'img' => 'test.jpg',
        ];
    }
}
