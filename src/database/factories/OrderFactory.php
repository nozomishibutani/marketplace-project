<?php

namespace Database\Factories;


use App\Models\User;
use App\Models\Item;
use App\Models\Order;
use Faker\Generator as Faker;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
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
        // ハイフン削除
        'postcode' => str_replace('-', '', $faker->postcode()),
        'address' => $faker->streetAddress(),
        'building' => $faker->secondaryAddress(),
        'payment_id' => $faker->unique()->uuid(),
        // コンビニの場合: requires_action
        // カードの場合: paid
        'payment_status' => 'OK',
        'created_at' => now(),
        'updated_at' => now()
    ];
    }
}
