<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('user_status')->truncate();
        DB::table('users')->truncate();

        DB::table('user_status')->insert([
            [
                'id' => 1,
                'name' => 'Pendente',
                'description' => 'Usuário ainda não finalizou o cadastro',
                'color' => 'yellow'
            ],
            [
                'id' => 2,
                'name' => 'Ativo',
                'description' => 'Usuário finalizou o cadastro',
                'color' => 'green'
            ],
            [
                'id' => 3,
                'name' => 'Bloqueado',
                'description' => 'Usuário aprontou altas e boas',
                'color' => 'red'
            ]
        ]);

        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Lucas Souza',
                'email' => 'lucas@winkle.app',
                'password' => Hash::make('password'),
                'master_password' => Hash::make('password'),
                'status_id' => 2,
                'admin' => true
            ],
            [
                'id' => 2,
                'name' => 'Ayla Souza',
                'email' => 'ayla@winkle.app',
                'password' => Hash::make('password'),
                'master_password' => Hash::make('password'),
                'status_id' => 2,
                'admin' => false
            ],
            [
                'id' => 3,
                'name' => 'Lauro Yoshimoto',
                'email' => 'lauro@winkle.app',
                'password' => Hash::make('password'),
                'master_password' => Hash::make('password'),
                'status_id' => 2,
                'admin' => false
            ],
            [
                'id' => 4,
                'name' => 'Renan Junior',
                'email' => 'renan@winkle.app',
                'password' => Hash::make('password'),
                'master_password' => Hash::make('password'),
                'status_id' => 2,
                'admin' => false
            ],
            [
                'id' => 5,
                'name' => 'Victor Heid Kunamitsu Miko',
                'email' => 'victor@winkle.app',
                'password' => Hash::make('password'),
                'master_password' => Hash::make('password'),
                'status_id' => 2,
                'admin' => false
            ],
            [
                'id' => 6,
                'name' => 'Laís Ferreira de Souza',
                'email' => 'lais@winkle.app',
                'password' => Hash::make('password'),
                'master_password' => Hash::make('password'),
                'status_id' => 2,
                'admin' => false
            ]
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
