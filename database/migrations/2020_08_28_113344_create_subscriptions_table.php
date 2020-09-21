<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('subscriptions');
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('subscription_id')->unique();
            $table->string('package_id', 128);
            $table->string('plan_id', 128);
            $table->foreignId('customer_id')->references('id')->on('customers');
            $table->string('external_customer_id', 128);
            $table->decimal('price', 8, 2);
            $table->string('interval', 128);
            $table->string('interval_unit', 128);
            $table->integer('billing_cycles');
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
        Schema::dropIfExists('subscriptions');
    }
}
