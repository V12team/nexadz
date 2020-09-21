<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacebookCampaigns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('facebook_campaigns');
        Schema::create('facebook_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_id', 128);
            $table->string('ad_set_id' ,128);
            $table->string('name' ,128);
            $table->decimal('budget', 8, 2);
            $table->enum('status', ['IN_PROGRESS', 'ACTIVE', 'PAUSED', 'UNFUNDED', 'ERROR']);
            $table->enum('type' ,['Display ads', 'Retargeting', 'Dynamic Retargeting']);
            $table->string('objective' ,128);
            $table->text('data');
            $table->enum('network', ['facebook', 'instagram']);
            $table->foreignId('ad_account_id')->references('id')->on('ad_accounts');
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
        Schema::dropIfExists('facebook_campaigns');
    }
}
