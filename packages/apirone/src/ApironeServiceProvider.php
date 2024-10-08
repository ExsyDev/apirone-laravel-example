<?php

namespace Apirone;

use Apirone\SDK\Invoice;
use Apirone\SDK\Model\Settings;
use Illuminate\Support\ServiceProvider;

class ApironeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/apirone.php', 'apirone'
        );

        $this->app->singleton('apirone', function () {
            return new ApironeManager();
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/apirone.php' => config_path('apirone.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__.'/../vendor/apirone/apirone-sdk-php/src/assets' => public_path('vendor/apirone'),
        ], 'public');
    }
}
