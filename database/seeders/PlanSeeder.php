<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlanSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('plans')->truncate();

        DB::table('plans')->insert([
            [
                'id' => 1,
                'name' => 'WinkleMensal',
                'duration' => 1,
            ],
            [
                'id' => 2,
                'name' => 'WinkleAnual',
                'duration' => 12,
            ],
            [
                'id' => 3,
                'name' => 'WinkleEmpresarialMensal',
                'duration' => 1,
            ],
            [
                'id' => 4,
                'name' => 'WinkleEmpresarialAnual',
                'duration' => 12,
            ],
            [
                'id' => 5,
                'name' => 'Vitalício',
                'duration' => 9999,
            ]
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
