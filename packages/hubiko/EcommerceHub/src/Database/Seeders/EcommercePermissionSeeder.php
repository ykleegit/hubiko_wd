<?php

namespace Hubiko\EcommerceHub\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class EcommercePermissionSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $permissions = [
            'ecommerce dashboard manage',
            'ecommerce store manage',
            'ecommerce store create',
            'ecommerce store edit',
            'ecommerce store delete',
            'ecommerce store show',
            'ecommerce product manage',
            'ecommerce product create',
            'ecommerce product edit',
            'ecommerce product delete',
            'ecommerce product show',
            'ecommerce category manage',
            'ecommerce category create',
            'ecommerce category edit',
            'ecommerce category delete',
            'ecommerce order manage',
            'ecommerce order create',
            'ecommerce order edit',
            'ecommerce order delete',
            'ecommerce order show',
            'ecommerce customer manage',
            'ecommerce customer create',
            'ecommerce customer edit',
            'ecommerce customer delete',
            'ecommerce customer show',
            'ecommerce coupon manage',
            'ecommerce coupon create',
            'ecommerce coupon edit',
            'ecommerce coupon delete',
            'ecommerce payment manage',
            'ecommerce settings manage',
            'ecommerce reports view'
        ];

        $company_role = Role::where('name', 'company')->first();
        $super_admin_role = Role::where('name', 'super admin')->first();

        foreach ($permissions as $permission_name) {
            $permission = Permission::where('name', $permission_name)->first();
            if (!$permission) {
                $permission = Permission::create([
                    'name' => $permission_name,
                    'display_name' => ucwords($permission_name),
                    'description' => ucwords($permission_name)
                ]);
            }

            // Assign to company role
            if ($company_role) {
                $company_role->permissions()->syncWithoutDetaching([$permission->id]);
            }

            // Assign to super admin role
            if ($super_admin_role) {
                $super_admin_role->permissions()->syncWithoutDetaching([$permission->id]);
            }
        }
    }
}
