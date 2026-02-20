<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Generator as Faker;


class FavoriteFactory extends Factory
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
            'item_id' => Item::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
