<?php

namespace Hubiko\CompanySecretary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comp_sec_documents';

    protected $fillable = [
        'title',
        'description',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'file_extension',
        'person_id',
        'company_id',
        'registration_id',
        'is_verified',
        'verified_by',
        'verified_at',
        'workspace',
        'created_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the person associated with the document.
     */
    public function person()
    {
        return $this->belongsTo(DirectorShareholder::class, 'person_id');
    }

    /**
     * Get the company associated with the document.
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Get the registration associated with the document.
     */
    public function registration()
    {
        return $this->belongsTo(CompanyRegistration::class, 'registration_id');
    }

    /**
     * Get the user who verified the document.
     */
    public function verifier()
    {
        return $this->belongsTo(\App\Models\User::class, 'verified_by');
    }

    /**
     * Get the user who created the document.
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scope to filter by verified documents.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to filter by unverified documents.
     */
    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    /**
     * Scope to filter by document type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by workspace.
     */
    public function scopeWorkspace($query)
    {
        return $query->where('workspace', getActiveWorkSpace());
    }

    /**
     * Get the full URL to the document file.
     */
    public function getFileUrl()
    {
        if (!$this->file_path) {
            return null;
        }

        return asset('storage/' . $this->file_path);
    }

    /**
     * Get a human-readable file size.
     */
    public function getHumanReadableSize()
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.2f", $bytes / pow(1024, $factor)) . ' ' . $units[$factor];
    }

    /**
     * Get document type label.
     */
    public function getTypeLabel()
    {
        $types = [
            'incorporation_certificate' => __('Incorporation Certificate'),
            'business_registration' => __('Business Registration Certificate'),
            'memorandum' => __('Memorandum of Association'),
            'articles' => __('Articles of Association'),
            'director_id' => __('Director ID Copy'),
            'shareholder_id' => __('Shareholder ID Copy'),
            'address_proof' => __('Address Proof'),
            'bank_statement' => __('Bank Statement'),
            'annual_return' => __('Annual Return'),
            'other' => __('Other'),
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * Get verification status badge color.
     */
    public function getVerificationColorAttribute()
    {
        return $this->is_verified ? 'success' : 'warning';
    }

    /**
     * Get verification status label.
     */
    public function getVerificationStatusAttribute()
    {
        return $this->is_verified ? __('Verified') : __('Pending Verification');
    }
}
