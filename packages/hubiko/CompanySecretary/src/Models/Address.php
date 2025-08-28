<?php

namespace Hubiko\CompanySecretary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comp_sec_addresses';

    protected $fillable = [
        'company_id',
        'person_id',
        'type',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'is_primary',
        'workspace',
        'created_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the company associated with the address.
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Get the person associated with the address.
     */
    public function person()
    {
        return $this->belongsTo(DirectorShareholder::class, 'person_id');
    }

    /**
     * Get the user who created the address.
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scope to filter by workspace.
     */
    public function scopeWorkspace($query)
    {
        return $query->where('workspace', getActiveWorkSpace());
    }

    /**
     * Scope to filter by primary addresses.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to filter by address type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the full formatted address.
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get address type label.
     */
    public function getTypeLabel()
    {
        $types = [
            'registered' => __('Registered Address'),
            'business' => __('Business Address'),
            'correspondence' => __('Correspondence Address'),
            'residential' => __('Residential Address'),
        ];

        return $types[$this->type] ?? $this->type;
    }
}
