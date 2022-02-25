<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SaleSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('sale_status')->truncate();
        DB::table('sales')->truncate();

        DB::table('sale_status')->insert([
            [
                'id' => 1,
                'name' => 'Aguardando pagamento',
                'description' => 'o comprador iniciou a transação, mas até o momento o PagSeguro não recebeu nenhuma informação sobre o pagamento.'
            ],
            [
                'id' => 2,
                'name' => 'Em análise',
                'description' => 'o comprador optou por pagar com um cartão de crédito e o PagSeguro está analisando o risco da transação.'
            ],
            [
                'id' => 3,
                'name' => 'Paga',
                'description' => 'a transação foi paga pelo comprador e o PagSeguro já recebeu uma confirmação da instituição financeira responsável pelo processamento.'
            ],
            [
                'id' => 4,
                'name' => 'Disponível',
                'description' => 'a transação foi paga e chegou ao final de seu prazo de liberação sem ter sido retornada e sem que haja nenhuma disputa aberta.'
            ],
            [
                'id' => 5,
                'name' => 'Em disputa',
                'description' => 'o comprador, dentro do prazo de liberação da transação, abriu uma disputa.'
            ],
            [
                'id' => 6,
                'name' => 'Devolvida',
                'description' => 'o valor da transação foi devolvido para o comprador.'
            ],
            [
                'id' => 7,
                'name' => 'Cancelada',
                'description' => 'a transação foi cancelada sem ter sido finalizada.'
            ]
        ]);

        DB::table('sales')->insert([
            [
                'id' => 1,
                'user_id' => 1,
                'created_at' => Carbon::now(),
                'code' => 'VENDAUSER1',
                'plan_id' => 5,
                'status_id' => 4,
                'updated_at' => Carbon::now(),
                'value_total' => 0,
                'final_value' => 0,
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'created_at' => Carbon::now(),
                'code' => 'VENDAUSER2',
                'plan_id' => 5,
                'status_id' => 4,
                'updated_at' => Carbon::now(),
                'value_total' => 0,
                'final_value' => 0,
            ],
            [
                'id' => 3,
                'user_id' => 3,
                'created_at' => Carbon::now(),
                'code' => 'VENDAUSER3',
                'plan_id' => 5,
                'status_id' => 4,
                'updated_at' => Carbon::now(),
                'value_total' => 0,
                'final_value' => 0,
            ],
            [
                'id' => 4,
                'user_id' => 4,
                'created_at' => Carbon::now(),
                'code' => 'VENDAUSER4',
                'plan_id' => 5,
                'status_id' => 4,
                'updated_at' => Carbon::now(),
                'value_total' => 0,
                'final_value' => 0,
            ],
            [
                'id' => 5,
                'user_id' => 5,
                'created_at' => Carbon::now(),
                'code' => 'VENDAUSER5',
                'plan_id' => 5,
                'status_id' => 4,
                'updated_at' => Carbon::now(),
                'value_total' => 0,
                'final_value' => 0,
            ],
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
