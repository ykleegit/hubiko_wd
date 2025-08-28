<?php

namespace Hubiko\CompanySecretary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DirectorShareholder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comp_sec_directors_shareholders';

    protected $fillable = [
        'company_id',
        'registration_id',
        'type',
        'name',
        'id_number',
        'nationality',
        'address',
        'email',
        'phone',
        'appointment_date',
        'resignation_date',
        'shares',
        'percentage',
        'position',
        'status',
        'workspace',
        'created_by',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'resignation_date' => 'date',
        'shares' => 'integer',
        'percentage' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the company associated with the director/shareholder.
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Get the registration associated with the director/shareholder.
     */
    public function registration()
    {
        return $this->belongsTo(CompanyRegistration::class, 'registration_id');
    }

    /**
     * Get the documents associated with the director/shareholder.
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'person_id');
    }

    /**
     * Get the AML screening associated with the director/shareholder.
     */
    public function amlScreening()
    {
        return $this->hasOne(AmlScreening::class, 'person_id');
    }

    /**
     * Get the user who created the record.
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scope to filter by directors only.
     */
    public function scopeDirectors($query)
    {
        return $query->whereIn('type', ['director', 'both']);
    }

    /**
     * Scope to filter by shareholders only.
     */
    public function scopeShareholders($query)
    {
        return $query->whereIn('type', ['shareholder', 'both']);
    }

    /**
     * Scope to filter by active status.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by workspace.
     */
    public function scopeWorkspace($query)
    {
        return $query->where('workspace', getActiveWorkSpace());
    }

    /**
     * Check if the person is a director.
     */
    public function isDirector()
    {
        return in_array($this->type, ['director', 'both']);
    }

    /**
     * Check if the person is a shareholder.
     */
    public function isShareholder()
    {
        return in_array($this->type, ['shareholder', 'both']);
    }

    /**
     * Get type label.
     */
    public function getTypeLabel()
    {
        $types = [
            'director' => __('Director'),
            'shareholder' => __('Shareholder'),
            'both' => __('Director & Shareholder'),
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'active' => 'success',
            'resigned' => 'warning',
            'terminated' => 'danger',
        ];

        return $colors[$this->status] ?? 'primary';
    }

    /**
     * Get formatted shares display.
     */
    public function getFormattedSharesAttribute()
    {
        if (!$this->shares) {
            return 'N/A';
        }

        return number_format($this->shares) . ($this->percentage ? " ({$this->percentage}%)" : '');
    }
}
