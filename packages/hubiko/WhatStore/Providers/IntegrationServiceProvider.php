<?php

namespace Hubiko\WhatStore\Providers;

use Illuminate\Support\ServiceProvider;
use Hubiko\WhatStore\Services\Email\EmailService;
use Hubiko\WhatStore\Services\Payment\PaymentGatewayFactory;
use Hubiko\WhatStore\Services\SocialMedia\SocialMediaService;
use Hubiko\WhatStore\Services\Webhook\WebhookService;

class IntegrationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register Payment Gateway Factory
        $this->app->singleton('whatstore.payment', function ($app) {
            return new PaymentGatewayFactory();
        });

        // Register Webhook Service
        $this->app->singleton('whatstore.webhook', function ($app) {
            return new WebhookService();
        });

        // Register Email Service
        $this->app->singleton('whatstore.email', function ($app) {
            return new EmailService();
        });

        // Register Social Media Service
        $this->app->singleton('whatstore.social', function ($app) {
            return new SocialMediaService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Load views
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'whatstore');

        // Publish configuration files
        $this->publishes([
            __DIR__ . '/../config/whatstore.php' => config_path('whatstore.php'),
        ], 'whatstore-config');

        // Publish view files
        $this->publishes([
            __DIR__ . '/../Resources/views' => resource_path('views/vendor/whatstore'),
        ], 'whatstore-views');
    }
} 