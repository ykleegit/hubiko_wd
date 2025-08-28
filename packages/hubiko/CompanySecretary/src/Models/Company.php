<?php

namespace Hubiko\CompanySecretary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comp_sec_companies';

    protected $fillable = [
        'company_name_en',
        'company_name_zh',
        'business_registration_number',
        'incorporation_number',
        'incorporation_date',
        'business_registration_expiry',
        'company_type',
        'business_nature',
        'annual_return_date',
        'total_shares',
        'share_classes',
        'registration_id',
        'business_address',
        'registered_address',
        'status',
        'workspace',
        'created_by',
    ];

    protected $casts = [
        'incorporation_date' => 'date',
        'business_registration_expiry' => 'date',
        'annual_return_date' => 'date',
        'total_shares' => 'integer',
        'share_classes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the registration record associated with the company.
     */
    public function registration()
    {
        return $this->belongsTo(CompanyRegistration::class, 'registration_id');
    }

    /**
     * Get the directors and shareholders associated with the company.
     */
    public function directorsAndShareholders()
    {
        return $this->hasMany(DirectorShareholder::class, 'company_id');
    }

    /**
     * Get only directors associated with the company.
     */
    public function directors()
    {
        return $this->directorsAndShareholders()->where('type', 'director')->orWhere('type', 'both');
    }

    /**
     * Get only shareholders associated with the company.
     */
    public function shareholders()
    {
        return $this->directorsAndShareholders()->where('type', 'shareholder')->orWhere('type', 'both');
    }

    /**
     * Get the documents associated with the company.
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'company_id');
    }

    /**
     * Get the addresses associated with the company.
     */
    public function addresses()
    {
        return $this->hasMany(Address::class, 'company_id');
    }

    /**
     * Get the primary address of the company.
     */
    public function primaryAddress()
    {
        return $this->addresses()->where('is_primary', true)->first();
    }

    /**
     * Get the AML screenings associated with the company.
     */
    public function amlScreenings()
    {
        return $this->hasMany(AmlScreening::class, 'company_id');
    }

    /**
     * Get the government forms associated with the company.
     */
    public function governmentForms()
    {
        return $this->hasMany(GovernmentForm::class, 'company_id');
    }

    /**
     * Get the referrals associated with the company.
     */
    public function referrals()
    {
        return $this->hasMany(Referral::class, 'company_id');
    }

    /**
     * Get the user who created the company.
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get company type label.
     */
    public function getCompanyTypeLabel()
    {
        $types = [
            'private_limited' => __('Private Limited Company'),
            'public_limited' => __('Public Limited Company'),
            'partnership' => __('Partnership'),
            'sole_proprietorship' => __('Sole Proprietorship'),
        ];

        return $types[$this->company_type] ?? $this->company_type;
    }

    /**
     * Check if BR certificate has expired.
     */
    public function hasBrExpired()
    {
        if (!$this->business_registration_expiry) {
            return false;
        }

        return $this->business_registration_expiry->isPast();
    }

    /**
     * Check if BR certificate is expiring soon (within 30 days).
     */
    public function isBrExpiringSoon()
    {
        if (!$this->business_registration_expiry) {
            return false;
        }

        return !$this->hasBrExpired() && $this->business_registration_expiry->diffInDays(now()) <= 30;
    }

    /**
     * Check if annual return date is coming up (within 30 days).
     */
    public function isAnnualReturnComing()
    {
        if (!$this->annual_return_date) {
            return false;
        }

        $nextAnnualReturn = $this->getNextAnnualReturnDate();
        return $nextAnnualReturn && $nextAnnualReturn->diffInDays(now()) <= 30;
    }

    /**
     * Get the next annual return date.
     */
    public function getNextAnnualReturnDate()
    {
        if (!$this->annual_return_date) {
            return null;
        }

        $date = $this->annual_return_date->copy();

        // If the date has passed this year, get next year's date
        if ($date->setYear(now()->year)->isPast()) {
            return $date->setYear(now()->year + 1);
        }

        return $date->setYear(now()->year);
    }

    /**
     * Get the company name attribute for compatibility.
     */
    public function getNameAttribute()
    {
        return $this->company_name_en ?? '';
    }

    /**
     * Scope to filter by workspace.
     */
    public function scopeWorkspace($query)
    {
        return $query->where('workspace', getActiveWorkSpace());
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'active' => 'success',
            'inactive' => 'warning',
            'suspended' => 'danger',
            'dissolved' => 'secondary',
        ];

        return $colors[$this->status] ?? 'primary';
    }
}
