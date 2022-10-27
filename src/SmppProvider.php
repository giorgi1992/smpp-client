<?php

namespace Gko\Smpp;

use Illuminate\Support\ServiceProvider;

class SmppProvider extends ServiceProvider
{

    /**
     * Boot service provider.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/smpp-client.php' => config_path('smpp-client'),
        ]);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom( __DIR__ . '/../config/smpp-client.php', 'smpp-client-default');
        $this->app->bind(SmppInterface::class, Smpp::class);
    }

}
