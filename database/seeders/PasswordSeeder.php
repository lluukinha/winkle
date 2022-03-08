<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PasswordSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('folders')->truncate();
        DB::table('passwords')->truncate();

        DB::table('folders')->insert([
            [
                'id' => 1,
                'name' => 'REDES SOCIAIS',
                'user_id' => 1,
            ]
        ]);

        DB::table('passwords')->insert([
            [
                'id' => 1,
                'name' => 'Winkle',
                'url' => 'https://www.winkle.com.br',
                'description' => 'Is about this website',
                'user_id' => 1,
                'folder_id' => 1
            ]
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
