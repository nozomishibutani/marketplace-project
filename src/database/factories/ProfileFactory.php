<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Profile;
use App\Models\User;
use Faker\Generator as Faker;

class ProfileFactory extends Factory
{
    /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
    protected $model = Profile::class;

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
            // ハイフン削除
            'postcode' => str_replace('-', '', $faker->postcode()),
            'address' => $faker->streetAddress(),
            'building' => $faker->secondaryAddress(),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
