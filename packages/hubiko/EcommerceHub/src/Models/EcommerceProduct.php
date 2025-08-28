<?php

namespace Hubiko\EcommerceHub\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EcommerceProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'sku',
        'price',
        'sale_price',
        'cost_price',
        'stock_quantity',
        'manage_stock',
        'stock_status',
        'weight',
        'dimensions',
        'category_id',
        'brand_id',
        'tags',
        'images',
        'featured_image',
        'gallery_images',
        'is_featured',
        'is_digital',
        'downloadable_files',
        'status',
        'visibility',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'store_id',
        'created_by'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_digital' => 'boolean',
        'manage_stock' => 'boolean',
        'tags' => 'array',
        'images' => 'array',
        'gallery_images' => 'array',
        'downloadable_files' => 'array',
        'dimensions' => 'array'
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(EcommerceStore::class, 'store_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EcommerceCategory::class, 'category_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(EcommerceOrderItem::class, 'product_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_status', 'in_stock');
    }

    public function getFinalPriceAttribute()
    {
        return $this->sale_price ?: $this->price;
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->sale_price && $this->price > $this->sale_price) {
            return round((($this->price - $this->sale_price) / $this->price) * 100);
        }
        return 0;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
