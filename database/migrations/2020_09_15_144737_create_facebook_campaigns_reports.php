<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacebookCampaignsReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facebook_campaigns_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->references('id')->on('facebook_campaigns');
            $table->string('facebook_campaigns_id', 128);
            $table->integer('impression');
            $table->integer('click');
            $table->integer('cpc');
            $table->integer('cpm');
            $table->integer('cpp');
            $table->integer('ctr');
            $table->decimal('spend', 8, 2);
            $table->string('publisher_platform');
            $table->date('date');
            $table->integer('conversion');
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
        Schema::dropIfExists('facebook_campaigns_reports');
    }
}
