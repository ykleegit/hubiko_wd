<?php

namespace Hubiko\CompanySecretary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AmlScreening extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comp_sec_aml_screenings';

    protected $fillable = [
        'person_id',
        'company_id',
        'status',
        'reference_number',
        'screening_source',
        'result',
        'risk_score',
        'notes',
        'follow_up_action',
        'screened_at',
        'screened_by',
        'auto_manual_flag',
        'attachment_path',
        'is_verified',
        'verified_by',
        'verified_at',
        'workspace',
        'created_by',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'risk_score' => 'decimal:2',
        'screened_at' => 'datetime',
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the person associated with the AML screening.
     */
    public function person()
    {
        return $this->belongsTo(DirectorShareholder::class, 'person_id');
    }

    /**
     * Get the company associated with the AML screening.
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Get the user who verified the screening.
     */
    public function verifier()
    {
        return $this->belongsTo(\App\Models\User::class, 'verified_by');
    }

    /**
     * Get the user who created the screening.
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who performed the screening.
     */
    public function screener()
    {
        return $this->belongsTo(\App\Models\User::class, 'screened_by');
    }

    /**
     * Scope to filter by pending status.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter by in-progress status.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope to filter by completed status.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to filter by failed status.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to filter by verified screenings.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to filter by unverified screenings.
     */
    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    /**
     * Scope to filter by workspace.
     */
    public function scopeWorkspace($query)
    {
        return $query->where('workspace', getActiveWorkSpace());
    }

    /**
     * Get status label.
     */
    public function getStatusLabel()
    {
        $statuses = [
            'pending' => __('Pending'),
            'in_progress' => __('In Progress'),
            'completed' => __('Completed'),
            'failed' => __('Failed'),
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'in_progress' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Get risk level based on score.
     */
    public function getRiskLevel()
    {
        if (!$this->risk_score) {
            return 'Unknown';
        }

        if ($this->risk_score >= 80) {
            return 'High';
        } elseif ($this->risk_score >= 50) {
            return 'Medium';
        } else {
            return 'Low';
        }
    }

    /**
     * Get risk level color.
     */
    public function getRiskLevelColor()
    {
        $level = $this->getRiskLevel();
        
        $colors = [
            'High' => 'danger',
            'Medium' => 'warning',
            'Low' => 'success',
            'Unknown' => 'secondary',
        ];

        return $colors[$level] ?? 'secondary';
    }

    /**
     * Generate unique reference number.
     */
    public static function generateReferenceNumber()
    {
        return 'AML-' . date('Y') . '-' . str_pad(static::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}
