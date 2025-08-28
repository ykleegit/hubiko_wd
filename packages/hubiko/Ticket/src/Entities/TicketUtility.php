<?php

namespace Hubiko\Ticket\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\Permission;
use App\Models\Role;

class TicketUtility extends Model
{
    /**
     * Setup default data when a new workspace is created
     */
    public static function defaultData($company_id = null, $workspace_id = null)
    {
        // Default Priorities
        $defaultPriorities = [
            [
                'name' => 'Low',
                'color' => '#38CB89'
            ],
            [
                'name' => 'Medium',
                'color' => '#FFAB2B'
            ],
            [
                'name' => 'High',
                'color' => '#F03D3D'
            ],
            [
                'name' => 'Critical',
                'color' => '#F90000'
            ]
        ];
        
        // Default Categories
        $defaultCategories = [
            [
                'name' => 'Technical',
                'color' => '#6571FF',
                'parent' => 0
            ],
            [
                'name' => 'Billing',
                'color' => '#38CB89',
                'parent' => 0
            ],
            [
                'name' => 'General',
                'color' => '#FFAB2B',
                'parent' => 0
            ],
            [
                'name' => 'Feature Request',
                'color' => '#F25767',
                'parent' => 0
            ]
        ];
        
        if(!$company_id || !$workspace_id) {
            return true;
        }

        // Create default priorities
        foreach ($defaultPriorities as $priority) {
            Priority::create([
                'name' => $priority['name'],
                'color' => $priority['color'],
                'created_by' => $company_id,
                'workspace' => $workspace_id
            ]);
        }
        
        // Create default categories
        foreach ($defaultCategories as $category) {
            Category::create([
                'name' => $category['name'],
                'color' => $category['color'],
                'parent' => $category['parent'],
                'created_by' => $company_id,
                'workspace' => $workspace_id
            ]);
        }
        
        return true;
    }
    
    /**
     * Assign module permissions to roles
     */
    public static function givePermissionToRoles($role_id = null, $rolename = null)
    {
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
        
        if($role_id) {
            $role = Role::find($role_id);
        } else {
            $role = Role::where('name', $rolename)->first();
        }
        
        if($role) {
            foreach ($permissions as $permission_name) {
                $permission = Permission::where('name', $permission_name)
                    ->where('module', 'Ticket')
                    ->first();
                    
                if($permission) {
                    $role->permissions()->syncWithoutDetaching([$permission->id]);
                }
            }
        }
        
        return true;
    }
} 