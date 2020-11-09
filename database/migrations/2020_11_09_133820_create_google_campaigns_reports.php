<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoogleCampaignsReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_campaigns_reports', function (Blueprint $table) {
            $table->id();
            $table->string('google_campaigns_id', 255);
            $table->date('date');
            $table->integer('impression');
            $table->integer('clicks');
            $table->double('costs');
            $table->double('str');
            $table->double('cpc');
            $table->double('cpm');
            $table->decimal('spend', 8, 2);
            $table->integer('click_conversions');
            $table->integer('view_conversions');
            $table->integer('conversions');
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
        Schema::dropIfExists('google_campaigns_reports');
    }
}
