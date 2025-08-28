<?php

namespace Hubiko\CompanySecretary\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegulatoryFiling extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_board_id',
        'filing_type',
        'filing_reference',
        'title',
        'description',
        'regulatory_body',
        'due_date',
        'filed_date',
        'status',
        'filing_fee',
        'penalty_amount',
        'documents',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'due_date' => 'date',
        'filed_date' => 'date',
        'filing_fee' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'documents' => 'array'
    ];

    public function companyBoard(): BelongsTo
    {
        return $this->belongsTo(CompanyBoard::class, 'company_board_id');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->where('status', '!=', 'filed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFiled($query)
    {
        return $query->where('status', 'filed');
    }

    public function getIsOverdueAttribute()
    {
        return $this->due_date < now() && $this->status !== 'filed';
    }

    public function getDaysUntilDueAttribute()
    {
        return $this->due_date ? now()->diffInDays($this->due_date, false) : null;
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'filed' => 'success',
            'pending' => 'warning',
            'overdue' => 'danger',
            default => 'secondary'
        };
    }
}
