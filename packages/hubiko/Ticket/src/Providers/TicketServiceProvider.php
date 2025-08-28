<?php

namespace Hubiko\Ticket\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Hubiko\Ticket\Ticket;

class TicketServiceProvider extends ServiceProvider
{
    protected $moduleName = 'Ticket';
    protected $moduleNameLower = 'ticket';

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
        
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php', 'ticket'
        );
        
        $this->app->singleton('ticket', function ($app) {
            return new Ticket();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', $this->moduleNameLower);
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->registerTranslations();
        
        // Register and publish assets
        $this->registerPublishing();
        
        // Load helpers
        $this->loadHelpers();
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(__DIR__ . '/../Resources/lang');
        }
    }

    /**
     * Register publishing resources.
     */
    protected function registerPublishing(): void
    {
        // Publish assets
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../Resources/assets' => public_path('modules/ticket'),
            ], 'ticket-assets');
            
            // Publish views
            $this->publishes([
                __DIR__ . '/../Resources/views' => resource_path('views/vendor/ticket'),
            ], 'ticket-views');
            
            // Publish config
            $this->publishes([
                __DIR__ . '/../Config/config.php' => config_path('ticket.php'),
            ], 'ticket-config');
            
            // Publish translations
            $this->publishes([
                __DIR__ . '/../Resources/lang' => resource_path('lang/modules/ticket'),
            ], 'ticket-lang');
            
            // Publish migrations
            $this->publishes([
                __DIR__ . '/../Database/Migrations' => database_path('migrations/ticket'),
            ], 'ticket-migrations');
        }
    }

    /**
     * Load helpers.
     */
    protected function loadHelpers(): void
    {
        if (file_exists(__DIR__ . '/../helpers.php')) {
            require_once __DIR__ . '/../helpers.php';
        }
    }

    /**
     * Register routes.
     */
    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'ticket');
    }

    /**
     * Register an additional directory of factories.
     */
    protected function registerFactories(): void
    {
        if (app()->environment('local')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Register all module events.
     */
    protected function registerEvents(): void
    {
        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['ticket'];
    }
} 