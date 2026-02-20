<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Generator as Faker;

class CategoryFactory extends Factory
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
        'name' => $faker->unique()->word() . rand(1, 100),
        ];
    }
}
