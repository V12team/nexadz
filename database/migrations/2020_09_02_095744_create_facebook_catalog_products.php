<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacebookCatalogProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('facebook_catalog_products');
        Schema::create('facebook_catalog_products', function (Blueprint $table) {
            $table->id();
            $table->string('catalog_id', 128);
            $table->string('product_feed_id', 128, 128);
            $table->string('product_set_id', 128);
            $table->string('feed_url');
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
        Schema::dropIfExists('facebook_catalog_products');
    }
}
