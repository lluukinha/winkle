<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Password;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
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
            'email' => 'lucas@winkle.app',
            'password' => Hash::make('password'),
            'master_password' => md5('password')
        ]);

        User::create([
            'id' => 2,
            'name' => 'Ayla Souza',
            'email' => 'ayla@winkle.app',
            'password' => Hash::make('password'),
            'master_password' => md5('password')
        ]);

        User::create([
            'id' => 3,
            'name' => 'Lauro Yoshimoto',
            'email' => 'lauro@winkle.app',
            'password' => Hash::make('password'),
            'master_password' => md5('password')
        ]);

        User::create([
            'id' => 4,
            'name' => 'Renan Junior',
            'email' => 'renan@winkle.app',
            'password' => Hash::make('password'),
            'master_password' => md5('password')
        ]);

        User::create([
            'id' => 5,
            'name' => 'Victor Heid Kunamitsu Miko',
            'email' => 'victor@winkle.app',
            'password' => Hash::make('password'),
            'master_password' => md5('password')
        ]);

        Password::create([
            'name' => 'Winkle',
            'url' => 'https://www.winkle.com.br',
            // 'login' => Crypt::encryptString('login'),
            // 'password' => Crypt::encryptString('password'),
            'description' => 'Is about this website',
            'user_id' => 1
        ]);
    }
}
