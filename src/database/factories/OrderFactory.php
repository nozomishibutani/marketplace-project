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
        'payment_id' => fake()->unique()->uuid(),
        'payment_method' => Order::PAYMENT_CONVENIENCE,
        'postcode' => str_replace('-', '', $faker->postcode()), // ハイフン削除
        'address' => $faker->streetAddress(),
        'building' => $faker->secondaryAddress(),
        'status' => '2', // 要確認
    ];
    }
}
