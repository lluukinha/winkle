<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Password;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'id' => 1,
            'name' => 'Lucas Souza',
            'email' => 'lucas.prog07@gmail.com',
            'password' => Hash::make('password')
        ]);

        Password::create([
            'name' => 'Winkle',
            'url' => 'https://www.winkle.com.br',
            'login' => 'lucas.prog07@gmail.com',
            'password' => 'password',
            'description' => 'Is about this website',
            'user_id' => 1
        ]);
    }
}
