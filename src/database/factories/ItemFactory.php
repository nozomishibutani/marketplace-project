<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
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
        'price' => rand(1, 999999),
        'condition' => rand(1, 4),
        'img' => 'items/dummy.png',
        'created_at' => now(),
        'updated_at' => now()
        ];
    }
}
