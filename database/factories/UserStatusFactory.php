<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->sentence(),
            'color' => $this->faker->colorName()
        ];
    }
}
