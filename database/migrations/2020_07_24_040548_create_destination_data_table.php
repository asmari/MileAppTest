<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDestinationDataTable extends Migration
{
    public function up()
    {
        Schema::create('destination_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('customer_name');
            $table->string('customer_address');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone');
            $table->string('customer_address_detail')->nullable();
            $table->string('customer_zip_code');
            $table->string('zone_code');
            $table->integer('organization_id');
            $table->string('location_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('destination_data');
    }
}
