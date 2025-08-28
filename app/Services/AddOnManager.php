<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class AddOnManager
{
    protected $addonsPath;
    protected $cacheKey = 'installed_addons';
    
    public function __construct()
    {
        $this->addonsPath = base_path('packages/hubiko');
    }
    
    /**
     * Get all available add-ons
     */
    public function getAvailableAddOns()
    {
        $addons = [];
        
        if (!File::exists($this->addonsPath)) {
            File::makeDirectory($this->addonsPath, 0755, true);
        }
        
        $directories = File::directories($this->addonsPath);
        
        foreach ($directories as $directory) {
            $moduleJsonPath = $directory . '/module.json';
            
            if (File::exists($moduleJsonPath)) {
                $moduleData = json_decode(File::get($moduleJsonPath), true);
                
                if ($moduleData) {
                    $moduleData['path'] = $directory;
                    $moduleData['installed'] = $this->isInstalled(basename($directory));
                    $moduleData['enabled'] = $this->isEnabled(basename($directory));
                    $addons[basename($directory)] = $moduleData;
                }
            }
        }
        
        return $addons;
    }
    
    /**
     * Install an add-on
     */
    public function installAddOn($addonName)
    {
        try {
            $addonPath = $this->addonsPath . '/' . $addonName;
            
            if (!File::exists($addonPath . '/module.json')) {
                throw new \Exception("Add-on {$addonName} not found or invalid.");
            }
            
            // Run migrations if they exist
            $migrationsPath = $addonPath . '/src/Database/Migrations';
            if (File::exists($migrationsPath)) {
                Artisan::call('migrate', [
                    '--path' => 'packages/hubiko/' . $addonName . '/src/Database/Migrations'
                ]);
            }
            
            // Run seeders if they exist
            $seedersPath = $addonPath . '/src/Database/Seeders';
            if (File::exists($seedersPath)) {
                $seederFiles = File::files($seedersPath);
                foreach ($seederFiles as $seederFile) {
                    $seederClass = 'Hubiko\\' . $addonName . '\\Database\\Seeders\\' . pathinfo($seederFile, PATHINFO_FILENAME);
                    if (class_exists($seederClass)) {
                        Artisan::call('db:seed', ['--class' => $seederClass]);
                    }
                }
            }
            
            // Mark as installed
            $this->markAsInstalled($addonName);
            
            // Clear cache
            Cache::forget($this->cacheKey);
            
            Log::info("Add-on {$addonName} installed successfully.");
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to install add-on {$addonName}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Uninstall an add-on
     */
    public function uninstallAddOn($addonName)
    {
        try {
            // Mark as uninstalled
            $this->markAsUninstalled($addonName);
            
            // Clear cache
            Cache::forget($this->cacheKey);
            
            Log::info("Add-on {$addonName} uninstalled successfully.");
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to uninstall add-on {$addonName}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Enable an add-on
     */
    public function enableAddOn($addonName)
    {
        $installedAddons = $this->getInstalledAddOns();
        
        if (isset($installedAddons[$addonName])) {
            $installedAddons[$addonName]['enabled'] = true;
            $this->saveInstalledAddOns($installedAddons);
            Cache::forget($this->cacheKey);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Disable an add-on
     */
    public function disableAddOn($addonName)
    {
        $installedAddons = $this->getInstalledAddOns();
        
        if (isset($installedAddons[$addonName])) {
            $installedAddons[$addonName]['enabled'] = false;
            $this->saveInstalledAddOns($installedAddons);
            Cache::forget($this->cacheKey);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if add-on is installed
     */
    public function isInstalled($addonName)
    {
        $installedAddons = $this->getInstalledAddOns();
        return isset($installedAddons[$addonName]);
    }
    
    /**
     * Check if add-on is enabled
     */
    public function isEnabled($addonName)
    {
        $installedAddons = $this->getInstalledAddOns();
        return isset($installedAddons[$addonName]) && $installedAddons[$addonName]['enabled'];
    }
    
    /**
     * Get installed add-ons from storage
     */
    protected function getInstalledAddOns()
    {
        $configPath = storage_path('app/addons.json');
        
        if (File::exists($configPath)) {
            return json_decode(File::get($configPath), true) ?: [];
        }
        
        return [];
    }
    
    /**
     * Save installed add-ons to storage
     */
    protected function saveInstalledAddOns($addons)
    {
        $configPath = storage_path('app/addons.json');
        File::put($configPath, json_encode($addons, JSON_PRETTY_PRINT));
    }
    
    /**
     * Mark add-on as installed
     */
    protected function markAsInstalled($addonName)
    {
        $installedAddons = $this->getInstalledAddOns();
        $installedAddons[$addonName] = [
            'installed_at' => now()->toDateTimeString(),
            'enabled' => true
        ];
        $this->saveInstalledAddOns($installedAddons);
    }
    
    /**
     * Mark add-on as uninstalled
     */
    protected function markAsUninstalled($addonName)
    {
        $installedAddons = $this->getInstalledAddOns();
        unset($installedAddons[$addonName]);
        $this->saveInstalledAddOns($installedAddons);
    }
    
    /**
     * Get enabled add-ons for service provider registration
     */
    public function getEnabledAddOns()
    {
        try {
            return Cache::remember($this->cacheKey, 3600, function () {
                return $this->getEnabledAddOnsFromStorage();
            });
        } catch (\Exception $e) {
            // Fallback for bootstrap phase when cache is not available
            return $this->getEnabledAddOnsFromStorage();
        }
    }
    
    /**
     * Get enabled add-ons from storage without cache
     */
    protected function getEnabledAddOnsFromStorage()
    {
        $enabledAddons = [];
        $availableAddons = $this->getAvailableAddOns();
        
        foreach ($availableAddons as $name => $addon) {
            if ($addon['enabled']) {
                $enabledAddons[$name] = $addon;
            }
        }
        
        return $enabledAddons;
    }
}
