<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('transaction_amount');
            $table->integer('transaction_discount')->nullable();
            $table->string('transaction_additional_field')->nullable();
            $table->integer('transaction_payment_type');
            $table->integer('transaction_cash_amount');
            $table->integer('transaction_cash_change');
            $table->string('transaction_state',50);
            $table->string('transaction_code',50);
            $table->integer('transaction_order');
            $table->string('transaction_payment_type_name',50);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
