<?php

namespace Hubiko\WhatStore;

use Illuminate\Support\ServiceProvider;
use Hubiko\WhatStore\Providers\EventServiceProvider;
use Hubiko\WhatStore\Providers\RouteServiceProvider;

class WhatStoreServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register the event service provider
        $this->app->register(EventServiceProvider::class);
        
        // Register the route service provider
        $this->app->register(RouteServiceProvider::class);
        
        // Load configuration
        $this->mergeConfigFrom(
            __DIR__ . '/config/whatstore.php', 'whatstore'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        
        // Load views
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'whatstore');
        
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        
        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/Resources/lang', 'whatstore');
        
        // Publish config
        $this->publishes([
            __DIR__ . '/config/whatstore.php' => config_path('whatstore.php'),
        ], 'whatstore-config');
        
        // Publish assets
        $this->publishes([
            __DIR__ . '/Resources/assets' => public_path('vendor/whatstore'),
        ], 'whatstore-assets');
        
        // Publish views
        $this->publishes([
            __DIR__ . '/Resources/views' => resource_path('views/vendor/whatstore'),
        ], 'whatstore-views');
    }
} 