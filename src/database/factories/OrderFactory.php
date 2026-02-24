<?php

namespace Database\Factories;


use App\Models\User;
use App\Models\Item;
use App\Models\Order;


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
        return [
        'user_id' => User::factory(),
        'item_id' => Item::factory(),
        'payment_method' => Order::PAYMENT_CONVENIENCE,
        'postcode' => '1234567',
        'address' => 'テストアドレス',
        'building' => 'テスト建物名',
        'status' => '2', // 要確認
    ];
    }
}
