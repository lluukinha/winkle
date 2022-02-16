<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FolderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $models = [ 'passwords', 'emails', 'notes' ];
        return [
            'name' => $this->faker->name(),
            'model' => array_rand($models),
            'user_id' => User::factory()->create()->id
        ];
    }
}
