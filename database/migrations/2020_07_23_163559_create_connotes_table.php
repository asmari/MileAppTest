<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConnotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('connotes', function (Blueprint $table) {
            $table->id();
            $table->string('connote_id');
            $table->integer('connote_number');
            $table->string('connote_service',20);
            $table->integer('connote_service_price');
            $table->integer('connote_amount');
            $table->string('connote_code',50);
            $table->string('connote_booking_code');
            $table->integer('connote_order');
            $table->string('connote_state',20);
            $table->integer('connote_state_id');
            $table->string('zone_code_from',20);
            $table->string('zone_code_to',20);
            $table->integer('surcharge_amount')->nullable();
            $table->string('transaction_id');
            $table->integer('actual_weight');
            $table->integer('volume_weight');
            $table->integer('chargeable_weight');
            $table->unsignedBigInteger('organization_id');
            $table->string('location_id');
            $table->string('connote_total_package');
            $table->string('connote_surcharge_amount');
            $table->string('connote_sla_day');
            $table->string('location_name');
            $table->string('location_type');
            $table->string('source_tariff_db');
            $table->string('id_source_tariff');
            $table->string('pod')->nullable();
            $table->text('history')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->onDelete('cascade');

            $table->foreign('transaction_id')
                ->references('transaction_id')
                ->on('transactions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('connotes');
    }
}
