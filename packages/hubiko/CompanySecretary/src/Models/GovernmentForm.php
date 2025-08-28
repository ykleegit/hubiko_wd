<?php

namespace Hubiko\CompanySecretary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GovernmentForm extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comp_sec_government_forms';

    protected $fillable = [
        'company_id',
        'title',
        'form_type',
        'document_id',
        'status',
        'form_data',
        'submission_date',
        'approval_date',
        'reference_number',
        'workspace',
        'created_by',
    ];

    protected $casts = [
        'form_data' => 'array',
        'submission_date' => 'date',
        'approval_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the company associated with the government form.
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Get the document associated with the government form.
     */
    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    /**
     * Get the user who created the government form.
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
     * Scope to filter by form type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('form_type', $type);
    }

    /**
     * Get the form type label.
     */
    public function getFormTypeLabel()
    {
        $types = [
            'nar1' => __('NAR1 - Notification of Appointment of Receiver'),
            'nd2a' => __('ND2A - Notice of Change of Directors'),
            'nsc1' => __('NSC1 - Return of Allotment'),
            'nr1' => __('NR1 - Annual Return'),
            'nnc1' => __('NNC1 - Incorporation Form'),
            'nd2b' => __('ND2B - Notice of Change of Company Secretary'),
            'nsc2' => __('NSC2 - Notice of Increase in Share Capital'),
            'nd3' => __('ND3 - Notice of Change of Registered Office'),
        ];

        return $types[$this->form_type] ?? $this->form_type;
    }

    /**
     * Get the status label.
     */
    public function getStatusLabel()
    {
        $statuses = [
            'draft' => __('Draft'),
            'generated' => __('Generated'),
            'submitted' => __('Submitted'),
            'approved' => __('Approved'),
            'rejected' => __('Rejected'),
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Get the status color class.
     */
    public function getStatusColorClass()
    {
        $colors = [
            'draft' => 'warning',
            'generated' => 'info',
            'submitted' => 'primary',
            'approved' => 'success',
            'rejected' => 'danger',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Generate unique reference number.
     */
    public static function generateReferenceNumber($formType)
    {
        $prefix = strtoupper($formType);
        return $prefix . '-' . date('Y') . '-' . str_pad(static::where('form_type', $formType)->count() + 1, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Check if form can be edited.
     */
    public function canBeEdited()
    {
        return in_array($this->status, ['draft', 'rejected']);
    }

    /**
     * Check if form can be submitted.
     */
    public function canBeSubmitted()
    {
        return in_array($this->status, ['draft', 'generated', 'rejected']);
    }
}
