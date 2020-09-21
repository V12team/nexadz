<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('ads_accounts');
        Schema::create('ads_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('ad_account_id', 128);
            $table->foreignId('customer_id')->references('id')->on('customers');
            $table->enum('ad_service', ['facebook_ads', 'adwords']);
            $table->decimal('budget', 8, 2);
            $table->enum('is_active', [0, 1]);
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
        Schema::dropIfExists('ad_accounts');
    }
}
