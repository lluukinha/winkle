<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('code')->unique();
            $table->foreignId('plan_id')->constrained('plans');
            $table->foreignId('status_id')->constrained('sale_status');
            $table->string('cancellation_source')->nullable(); //only if status == 7 - INTERNAL (pagseguro) / EXTERNAL (banks)
            $table->decimal('value_total'); // grossamount
            $table->decimal('final_value'); // gross - tax
            $table->longText('transaction_body')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales');
    }
}
