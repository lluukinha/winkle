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
        $this->call([
            UserSeeder::class,
            PasswordSeeder::class,
            PlanSeeder::class,
            SaleSeeder::class,
        ]);
    }
}
