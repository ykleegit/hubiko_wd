<?php

namespace Hubiko\SEOHub\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;

class SEOPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // SEO Dashboard
            'view seo dashboard',
            'manage seo settings',
            
            // SEO Websites
            'view seo websites',
            'create seo websites',
            'edit seo websites',
            'delete seo websites',
            'run seo audits',
            
            // SEO Audits
            'view seo audits',
            'create seo audits',
            'delete seo audits',
            'export seo audits',
            'refresh seo audits',
            
            // SEO Keywords
            'view seo keywords',
            'create seo keywords',
            'edit seo keywords',
            'delete seo keywords',
            'import seo keywords',
            'check keyword rankings',
            
            // SEO Issues
            'view seo issues',
            'manage seo issues',
            'fix seo issues',
            'ignore seo issues',
            
            // SEO Reports
            'view seo reports',
            'create seo reports',
            'edit seo reports',
            'delete seo reports',
            'generate seo reports',
            'download seo reports',
            
            // SEO Competitors
            'view seo competitors',
            'create seo competitors',
            'edit seo competitors',
            'delete seo competitors',
            'analyze seo competitors',
            
            // SEO Monitoring
            'view seo monitoring',
            'configure seo monitoring',
            'view seo analytics',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'display_name' => ucwords($permission), 'description' => ucwords($permission)]);
        }
    }
}
