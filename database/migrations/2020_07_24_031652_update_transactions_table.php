<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTransactionsTable extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->text('custom_field');
            $table->string('top',50);
            $table->string('jenis_pelanggan',50);
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->text('custom_field')->nullable();
            $table->string('top',50)->default("14 Hari");
            $table->string('jenis_pelanggan',50)->default("B2B");
        });
    }
}
