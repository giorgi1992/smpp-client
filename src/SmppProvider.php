<?php

namespace Gko\Smpp;

use Illuminate\Support\ServiceProvider;

class SmppProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     */
    protected $defer = true;

    /**
     * Boot service provider.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/smpp-client.php' => config_path('smpp-client.php'),
        ]);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->bind(SmppInterface::class, Smpp::class);
    }
}
