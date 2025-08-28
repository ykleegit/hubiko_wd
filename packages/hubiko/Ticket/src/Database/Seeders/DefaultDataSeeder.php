<?php

namespace Hubiko\Ticket\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Hubiko\Ticket\Entities\Priority;
use Hubiko\Ticket\Entities\Category;

class DefaultDataSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();
        
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
        
        // Seed default priorities if they don't exist
        $adminId = 1; // Default system admin ID
        $workspaceId = 1; // Default workspace ID
        
        foreach ($defaultPriorities as $priority) {
            Priority::firstOrCreate(
                [
                    'name' => $priority['name'],
                    'workspace' => $workspaceId
                ],
                [
                    'color' => $priority['color'],
                    'created_by' => $adminId,
                    'workspace' => $workspaceId
                ]
            );
        }
        
        // Seed default categories
        foreach ($defaultCategories as $category) {
            Category::firstOrCreate(
                [
                    'name' => $category['name'],
                    'workspace' => $workspaceId
                ],
                [
                    'color' => $category['color'],
                    'parent' => $category['parent'],
                    'created_by' => $adminId,
                    'workspace' => $workspaceId
                ]
            );
        }
    }
} 