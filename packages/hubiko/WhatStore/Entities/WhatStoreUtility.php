<?php

namespace Hubiko\WhatStore\Entities;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;

class WhatStoreUtility extends Model
{
    /**
     * Setup default data for a new workspace
     *
     * @param int $company_id
     * @param int $workspace_id
     * @return void
     */
    public static function defaultData($company_id = null, $workspace_id = null)
    {
        // Create default product categories
        self::setupDefaultCategories($company_id, $workspace_id);
        
        // Create default settings
        self::setupDefaultSettings($company_id, $workspace_id);
        
        // Create default tax rates
        self::setupDefaultTaxRates($company_id, $workspace_id);
    }
    
    /**
     * Setup default categories
     *
     * @param int $company_id
     * @param int $workspace_id
     * @return void
     */
    protected static function setupDefaultCategories($company_id, $workspace_id)
    {
        $categories = [
            'Electronics',
            'Clothing',
            'Home & Kitchen',
            'Books',
            'Health & Beauty'
        ];
        
        foreach ($categories as $category) {
            ProductCategory::firstOrCreate(
                [
                    'name' => $category,
                    'company_id' => $company_id,
                    'workspace_id' => $workspace_id
                ],
                [
                    'slug' => \Illuminate\Support\Str::slug($category),
                    'description' => 'Default category for ' . $category,
                    'created_by' => $company_id
                ]
            );
        }
    }
    
    /**
     * Setup default settings
     *
     * @param int $company_id
     * @param int $workspace_id
     * @return void
     */
    protected static function setupDefaultSettings($company_id, $workspace_id)
    {
        $settings = [
            'default_currency' => 'USD',
            'default_tax_rate' => 0,
            'shipping_enabled' => true,
            'default_shipping_rate' => 0,
            'enable_guest_checkout' => true,
            'default_order_status' => 'pending',
        ];
        
        foreach ($settings as $key => $value) {
            Settings::firstOrCreate(
                [
                    'key' => $key,
                    'company_id' => $company_id,
                    'workspace_id' => $workspace_id
                ],
                [
                    'value' => $value,
                    'created_by' => $company_id
                ]
            );
        }
    }
    
    /**
     * Setup default tax rates
     *
     * @param int $company_id
     * @param int $workspace_id
     * @return void
     */
    protected static function setupDefaultTaxRates($company_id, $workspace_id)
    {
        $taxRates = [
            [
                'name' => 'No Tax',
                'rate' => 0,
            ],
            [
                'name' => 'Sales Tax',
                'rate' => 10,
            ]
        ];
        
        foreach ($taxRates as $taxRate) {
            ProductTax::firstOrCreate(
                [
                    'name' => $taxRate['name'],
                    'company_id' => $company_id,
                    'workspace_id' => $workspace_id
                ],
                [
                    'rate' => $taxRate['rate'],
                    'created_by' => $company_id
                ]
            );
        }
    }
    
    /**
     * Assign permissions to roles
     *
     * @param int|null $role_id
     * @param string|null $rolename
     * @return void
     */
    public static function givePermissionToRoles($role_id = null, $rolename = null)
    {
        $module = 'WhatStore';
        
        // Define all the permissions for this module
        $permissions = [
            'whatstore manage',
            'whatstore create',
            'whatstore edit',
            'whatstore delete',
            'whatstore show',
            'whatstore store create',
            'whatstore store edit',
            'whatstore store delete',
            'whatstore store show',
            'whatstore product create',
            'whatstore product edit',
            'whatstore product delete',
            'whatstore product show',
            'whatstore order create',
            'whatstore order edit',
            'whatstore order delete',
            'whatstore order show',
            'whatstore customer create',
            'whatstore customer edit',
            'whatstore customer delete',
            'whatstore customer show',
            'whatstore settings manage',
            'whatstore payment manage',
            'whatstore report view',
        ];
        
        // Get the role to assign permissions to
        if($role_id) {
            $role = Role::find($role_id);
        } else {
            $role = Role::where('name', $rolename)->first();
        }
        
        // If role exists, assign permissions
        if($role) {
            foreach ($permissions as $permission_name) {
                // Check if permission exists
                $permission = Permission::where('name', $permission_name)
                    ->where('module', $module)
                    ->first();
                
                // If permission doesn't exist, create it
                if(!$permission) {
                    $permission = Permission::create([
                        'name' => $permission_name,
                        'guard_name' => 'web',
                        'module' => $module,
                        'created_by' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
                
                // Give permission to role using Hubiko's method
                if(!$role->permissions->contains('name', $permission_name)) {
                    $role->permissions()->syncWithoutDetaching([$permission->id]);
                }
            }
        }
    }
} 