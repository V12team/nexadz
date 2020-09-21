<?php


namespace App\Providers;

use App\Services\FacebookAdsService;
use Illuminate\Support\ServiceProvider;


class FacebookServiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(FacebookAdsService::class, function ($app) {
            return new FacebookAdsService(
                env('FACEBOOK_APP_ID'),
                env('FACEBOOK_APP_SECRET'),
                env('FACEBOOK_TOKEN')
            );
        });
    }

    public function provides()
    {
        return [FacebookAdsService::class];
    }
}
