<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoogleCampaigns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_id', 255);
            $table->string('name' , 255);
            $table->decimal('budget', 8, 2);
            $table->enum('status', ['IN_PROGRESS', 'ACTIVE', 'PAUSED', 'UNFUNDED', 'ERROR']);
            $table->text('data');
            $table->foreignId('ad_account_id')->references('id')->on('ads_accounts');
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
        Schema::dropIfExists('google_campaigns');
    }
}
