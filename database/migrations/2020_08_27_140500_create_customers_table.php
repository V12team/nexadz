<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('customers');
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('first_name', 128);
            $table->string('last_name', 128);
            $table->string('email')->unique();
            $table->string('phone', 128);
            $table->string('company', 128);
            $table->string('fb_page', 128);
            $table->enum('fb_grant_status', ['no', 'yes', 'pending']);
            $table->string('country', 128);
            $table->string('state', 128);
            $table->string('zipcode', 20);
            $table->string('address', 128);
            $table->enum('status', ['active', 'suspend_payment', 'inactive', 'canceled']);
            $table->decimal('balance', 8, 2);
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
        Schema::dropIfExists('customers');
    }
}
