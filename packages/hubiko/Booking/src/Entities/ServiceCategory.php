<?php

namespace Hubiko\Booking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'color',
        'icon',
        'sort_order',
        'is_active',
        'created_by',
        'workspace'
    ];

    protected $dates = [
        'deleted_at'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Predefined categories for different business types
    const CATEGORIES = [
        'salon' => [
            'Hair Cut & Styling',
            'Hair Coloring',
            'Hair Treatment',
            'Nail Services',
            'Makeup',
            'Eyebrow & Lashes'
        ],
        'beauty' => [
            'Facial Treatment',
            'Skin Care',
            'Body Treatment',
            'Massage',
            'Waxing',
            'Permanent Makeup'
        ],
        'spa' => [
            'Relaxation Massage',
            'Therapeutic Massage',
            'Body Scrub',
            'Aromatherapy',
            'Hot Stone Therapy',
            'Couples Treatment'
        ],
        'medical' => [
            'General Consultation',
            'Specialist Consultation',
            'Diagnostic Tests',
            'Procedures',
            'Follow-up',
            'Emergency'
        ],
        'fitness' => [
            'Personal Training',
            'Group Classes',
            'Nutrition Consultation',
            'Fitness Assessment',
            'Rehabilitation',
            'Sports Therapy'
        ],
        'automotive' => [
            'Oil Change',
            'Tire Service',
            'Brake Service',
            'Engine Diagnostics',
            'Car Wash',
            'Detailing'
        ]
    ];

    /**
     * Get all services in this category
     */
    public function services()
    {
        return $this->hasMany(Service::class, 'category_id');
    }

    /**
     * Get the user who created this category
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scope to filter categories by workspace
     */
    public function scopeByWorkspace($query, $workspace)
    {
        return $query->where('workspace', $workspace);
    }

    /**
     * Scope to get active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get categories ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
