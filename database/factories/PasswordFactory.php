<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;



class PasswordFactory extends Factory
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
            'url' => $this->faker->domainName(),
            'login' => $this->faker->safeEmail(),
            'password' => Str::random(10),
            'description' => $this->faker->sentence(),
            'user_id' => User::factory()->create()->id
        ];
    }
}
