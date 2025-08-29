<?php

namespace Hubiko\AIContent\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // AI Content Dashboard
            [
                'name' => 'ai content dashboard',
                'guard_name' => 'web',
                'module' => 'AIContent',
                'created_at' => now(),
                'updated_at' => now()
            ],

            // AI Content Management
            [
                'name' => 'ai content manage',
                'guard_name' => 'web',
                'module' => 'AIContent',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'ai content create',
                'guard_name' => 'web',
                'module' => 'AIContent',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'ai content view',
                'guard_name' => 'web',
                'module' => 'AIContent',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'ai content edit',
                'guard_name' => 'web',
                'module' => 'AIContent',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'ai content delete',
                'guard_name' => 'web',
                'module' => 'AIContent',
                'created_at' => now(),
                'updated_at' => now()
            ],

            // AI Template Management
            [
                'name' => 'ai template manage',
                'guard_name' => 'web',
                'module' => 'AIContent',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'ai template create',
                'guard_name' => 'web',
                'module' => 'AIContent',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'ai template view',
                'guard_name' => 'web',
                'module' => 'AIContent',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'ai template edit',
                'guard_name' => 'web',
                'module' => 'AIContent',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'ai template delete',
                'guard_name' => 'web',
                'module' => 'AIContent',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Assign permissions to super admin role
        $superAdminRole = Role::where('name', 'super admin')->first();
        if ($superAdminRole) {
            $permissionNames = array_column($permissions, 'name');
            $permissionIds = Permission::whereIn('name', $permissionNames)->pluck('id')->toArray();
            
            foreach ($permissionIds as $permissionId) {
                $superAdminRole->permissions()->syncWithoutDetaching([$permissionId]);
            }
        }

        // Assign basic permissions to company role
        $companyRole = Role::where('name', 'company')->first();
        if ($companyRole) {
            $basicPermissions = [
                'ai content dashboard',
                'ai content manage',
                'ai content create',
                'ai content view',
                'ai content edit',
                'ai template view'
            ];
            
            $permissionIds = Permission::whereIn('name', $basicPermissions)->pluck('id')->toArray();
            
            foreach ($permissionIds as $permissionId) {
                $companyRole->permissions()->syncWithoutDetaching([$permissionId]);
            }
        }
    }
}