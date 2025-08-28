<?php

namespace Hubiko\WhatStore\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class QueryOptimizer
{
    /**
     * Apply eager loading optimizations to product queries
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $relations Relations to eager load
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function optimizeProductQuery(Builder $query, array $relations = [])
    {
        // Default relations to eager load
        $defaultRelations = [
            'categories',
            'variants',
            'images',
            'tax'
        ];
        
        $eagerLoad = array_merge($defaultRelations, $relations);
        
        return $query->with($eagerLoad);
    }
    
    /**
     * Apply eager loading optimizations to order queries
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $relations Relations to eager load
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function optimizeOrderQuery(Builder $query, array $relations = [])
    {
        // Default relations to eager load
        $defaultRelations = [
            'items',
            'customer',
            'items.product',
            'items.variant'
        ];
        
        $eagerLoad = array_merge($defaultRelations, $relations);
        
        return $query->with($eagerLoad);
    }
    
    /**
     * Cache product results to improve performance
     *
     * @param int $store_id
     * @param array $filters Optional filters
     * @param int $perPage
     * @param string $sortBy
     * @param string $sortOrder
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function cachedProductList($store_id, $filters = [], $perPage = 15, $sortBy = 'created_at', $sortOrder = 'desc')
    {
        $cacheKey = "store_products_{$store_id}_" . md5(json_encode([
            'filters' => $filters,
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder
        ]));
        
        return Cache::remember($cacheKey, now()->addMinutes(30), function() use ($store_id, $filters, $perPage, $sortBy, $sortOrder) {
            $query = Product::where('store_id', $store_id)
                ->where('workspace_id', getActiveWorkSpace());
            
            // Apply filters
            if (!empty($filters['category_id'])) {
                $query->whereHas('categories', function($q) use ($filters) {
                    $q->where('category_id', $filters['category_id']);
                });
            }
            
            if (!empty($filters['search'])) {
                $query->where(function($q) use ($filters) {
                    $q->where('name', 'LIKE', "%{$filters['search']}%")
                      ->orWhere('description', 'LIKE', "%{$filters['search']}%");
                });
            }
            
            // Apply optimizations
            $query = self::optimizeProductQuery($query);
            
            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);
            
            return $query->paginate($perPage);
        });
    }
    
    /**
     * Optimize database indexes
     * 
     * @return array Status of optimization
     */
    public static function optimizeDatabaseIndexes()
    {
        $status = [];
        
        // Check and add indexes to products table if needed
        if (!self::hasIndex('whatstore_products', 'whatstore_products_store_id_workspace_id_index')) {
            DB::statement('CREATE INDEX whatstore_products_store_id_workspace_id_index ON whatstore_products(store_id, workspace_id)');
            $status['products_index'] = 'Created';
        } else {
            $status['products_index'] = 'Already exists';
        }
        
        // Check and add indexes to orders table if needed
        if (!self::hasIndex('whatstore_orders', 'whatstore_orders_store_id_workspace_id_index')) {
            DB::statement('CREATE INDEX whatstore_orders_store_id_workspace_id_index ON whatstore_orders(store_id, workspace_id)');
            $status['orders_index'] = 'Created';
        } else {
            $status['orders_index'] = 'Already exists';
        }
        
        // Check and add indexes to customers table if needed
        if (!self::hasIndex('whatstore_customers', 'whatstore_customers_store_id_workspace_id_index')) {
            DB::statement('CREATE INDEX whatstore_customers_store_id_workspace_id_index ON whatstore_customers(store_id, workspace_id)');
            $status['customers_index'] = 'Created';
        } else {
            $status['customers_index'] = 'Already exists';
        }
        
        return $status;
    }
    
    /**
     * Check if an index exists on a table
     *
     * @param string $table
     * @param string $index
     * @return bool
     */
    private static function hasIndex($table, $index)
    {
        return DB::select(
            "SELECT 1
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
            AND table_name = '$table'
            AND index_name = '$index'"
        ) ? true : false;
    }
} 