<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use App\Services\AddOnManager;

class AddOnServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton(AddOnManager::class, function ($app) {
            return new AddOnManager();
        });
        
        // Register enabled add-on service providers
        $this->registerAddOnProviders();
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Load add-on routes, views, and other resources
        $this->loadAddOnResources();
    }
    
    /**
     * Register add-on service providers
     */
    protected function registerAddOnProviders()
    {
        $addOnManager = new AddOnManager();
        $enabledAddons = $addOnManager->getEnabledAddOns();
        
        foreach ($enabledAddons as $name => $addon) {
            $this->registerAddOnServiceProvider($name, $addon);
        }
    }
    
    /**
     * Register individual add-on service provider
     */
    protected function registerAddOnServiceProvider($name, $addon)
    {
        $providerPath = $addon['path'] . '/src/Providers/' . $name . 'ServiceProvider.php';
        
        if (File::exists($providerPath)) {
            $providerClass = "Hubiko\\{$name}\\Providers\\{$name}ServiceProvider";
            
            if (class_exists($providerClass)) {
                $this->app->register($providerClass);
            }
        }
    }
    
    /**
     * Load add-on resources
     */
    protected function loadAddOnResources()
    {
        $addOnManager = app(AddOnManager::class);
        $enabledAddons = $addOnManager->getEnabledAddOns();
        
        foreach ($enabledAddons as $name => $addon) {
            $this->loadAddOnRoutes($name, $addon);
            $this->loadAddOnViews($name, $addon);
            $this->loadAddOnTranslations($name, $addon);
        }
    }
    
    /**
     * Load add-on routes
     */
    protected function loadAddOnRoutes($name, $addon)
    {
        $routesPath = $addon['path'] . '/src/Routes';
        
        if (File::exists($routesPath)) {
            $webRoutes = $routesPath . '/web.php';
            $apiRoutes = $routesPath . '/api.php';
            
            if (File::exists($webRoutes)) {
                $this->loadRoutesFrom($webRoutes);
            }
            
            if (File::exists($apiRoutes)) {
                $this->loadRoutesFrom($apiRoutes);
            }
        }
    }
    
    /**
     * Load add-on views
     */
    protected function loadAddOnViews($name, $addon)
    {
        $viewsPath = $addon['path'] . '/src/Resources/views';
        
        if (File::exists($viewsPath)) {
            $this->loadViewsFrom($viewsPath, strtolower($name));
        }
    }
    
    /**
     * Load add-on translations
     */
    protected function loadAddOnTranslations($name, $addon)
    {
        $langPath = $addon['path'] . '/src/Resources/lang';
        
        if (File::exists($langPath)) {
            $this->loadTranslationsFrom($langPath, strtolower($name));
        }
    }
}
