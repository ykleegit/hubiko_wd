<?php

namespace Hubiko\CompanySecretary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Referral extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comp_sec_referrals';

    protected $fillable = [
        'company_id',
        'contact_name',
        'contact_email',
        'contact_phone',
        'referral_type',
        'referral_code',
        'status',
        'notes',
        'last_invited_at',
        'workspace',
        'created_by',
    ];

    protected $casts = [
        'last_invited_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the company associated with the referral.
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Get the user who created the referral.
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
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get the referral type label.
     */
    public function getTypeLabel()
    {
        $types = [
            'company_formation' => __('Company Formation'),
            'company_secretary' => __('Company Secretary'),
            'accounting' => __('Accounting'),
            'tax' => __('Tax'),
            'legal' => __('Legal Services'),
            'other' => __('Other'),
        ];

        return $types[$this->referral_type] ?? $this->referral_type;
    }

    /**
     * Get the status label.
     */
    public function getStatusLabel()
    {
        $statuses = [
            'pending' => __('Pending'),
            'contacted' => __('Contacted'),
            'converted' => __('Converted'),
            'declined' => __('Declined'),
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Get the status color class.
     */
    public function getStatusColorClass()
    {
        $colors = [
            'pending' => 'warning',
            'contacted' => 'info',
            'converted' => 'success',
            'declined' => 'danger',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Generate unique referral code.
     */
    public static function generateReferralCode()
    {
        return 'REF-' . strtoupper(substr(md5(uniqid()), 0, 8));
    }
}
