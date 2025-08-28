<?php

namespace Hubiko\Ticket\Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

class PermissionTableSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();
        Artisan::call('cache:clear');
        $module = 'Ticket';

        $permissions = [
            'ticket manage',
            'ticket create',
            'ticket edit',
            'ticket delete',
            'ticket show',
            'ticket category manage',
            'ticket category create',
            'ticket category edit',
            'ticket category delete',
            'ticket priority manage',
            'ticket priority create',
            'ticket priority edit',
            'ticket priority delete',
            'ticket reply',
            'ticket assign',
            'ticket change status',
            'ticket custom field manage',
            'ticket custom field create',
            'ticket custom field edit',
            'ticket custom field delete',
            'ticket dashboard',
            'ticket settings',
        ];

        $company_role = Role::where('name','company')->first();
        $admin_role = Role::where('name', 'super admin')->first();
        
        foreach ($permissions as $value)
        {
            $check = Permission::where('name', $value)->exists();
            if($check == false)
            {
                $permission = Permission::create([
                    'name' => $value,
                    'guard_name' => 'web',
                    'module' => $module,
                    'created_by' => 0,
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s')
                ]);
            }
            else
            {
                $permission = Permission::where('name', $value)->first();
            }
            
            // Assign permissions to roles using Hubiko's method
            if($company_role)
            {
                $company_role->permissions()->syncWithoutDetaching([$permission->id]);
            }
            
            if($admin_role)
            {
                $admin_role->permissions()->syncWithoutDetaching([$permission->id]);
            }
        }
    }
} 