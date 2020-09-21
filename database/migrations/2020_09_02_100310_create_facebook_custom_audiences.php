<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacebookCustomAudiences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('facebook_custom_audiences');
        Schema::create('facebook_custom_audiences', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128);
            $table->integer('retention_day');
            $table->string('audience_id', 128);
            $table->string('dynamic_audience_id', 128);
            $table->string('event_source_group_id', 128);
            $table->string('pixel_id', 128);
            $table->foreignId('customer_id')->references('id')->on('customers');
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
        Schema::dropIfExists('facebook_custom_audiences');
    }
}
