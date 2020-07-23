<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateLocationsTable extends Migration
{
    public function up()
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->string('transaction_id');

            $table->foreign('transaction_id')
                ->references('transaction_id')
                ->on('transactions')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('locations', function (Blueprint $table) {
            //
        });
    }
}
