<?php

namespace Database\Seeders;

use App\Models\Password;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        DB::table('folders')->truncate();
        DB::table('passwords')->truncate();
        DB::table('users')->truncate();

        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Lucas Souza',
                'email' => 'lucas@winkle.app',
                'password' => Hash::make('password'),
                'master_password' => Hash::make('password')
            ],
            [
                'id' => 2,
                'name' => 'Ayla Souza',
                'email' => 'ayla@winkle.app',
                'password' => Hash::make('password'),
                'master_password' => Hash::make('password')
            ],
            [
                'id' => 3,
                'name' => 'Lauro Yoshimoto',
                'email' => 'lauro@winkle.app',
                'password' => Hash::make('password'),
                'master_password' => Hash::make('password')
            ],
            [
                'id' => 4,
                'name' => 'Renan Junior',
                'email' => 'renan@winkle.app',
                'password' => Hash::make('password'),
                'master_password' => Hash::make('password')
            ],
            [
                'id' => 5,
                'name' => 'Victor Heid Kunamitsu Miko',
                'email' => 'victor@winkle.app',
                'password' => Hash::make('password'),
                'master_password' => Hash::make('password')
            ]
        ]);

        DB::table('folders')->insert([
            [
                'id' => 1,
                'name' => 'REDES SOCIAIS',
                'user_id' => 1,
                'model' => 'passwords'
            ]
        ]);

        Password::create([
            'name' => 'Winkle',
            'url' => 'https://www.winkle.com.br',
            'description' => 'Is about this website',
            'user_id' => 1,
            'folder_id' => 1
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
