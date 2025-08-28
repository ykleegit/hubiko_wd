<?php

namespace Hubiko\CompanySecretary\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resolution extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_board_id',
        'meeting_id',
        'resolution_number',
        'title',
        'description',
        'resolution_type',
        'proposed_by',
        'seconded_by',
        'voting_method',
        'votes_for',
        'votes_against',
        'votes_abstain',
        'status',
        'passed_date',
        'effective_date',
        'attachments',
        'created_by'
    ];

    protected $casts = [
        'votes_for' => 'integer',
        'votes_against' => 'integer',
        'votes_abstain' => 'integer',
        'passed_date' => 'date',
        'effective_date' => 'date',
        'attachments' => 'array'
    ];

    public function companyBoard(): BelongsTo
    {
        return $this->belongsTo(CompanyBoard::class, 'company_board_id');
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(BoardMeeting::class, 'meeting_id');
    }

    public function proposer(): BelongsTo
    {
        return $this->belongsTo(Director::class, 'proposed_by');
    }

    public function seconder(): BelongsTo
    {
        return $this->belongsTo(Director::class, 'seconded_by');
    }

    public function scopePassed($query)
    {
        return $query->where('status', 'passed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function getTotalVotesAttribute()
    {
        return $this->votes_for + $this->votes_against + $this->votes_abstain;
    }

    public function getPassingPercentageAttribute()
    {
        $total = $this->total_votes;
        return $total > 0 ? ($this->votes_for / $total) * 100 : 0;
    }

    public function getIsPassedAttribute()
    {
        return $this->votes_for > $this->votes_against;
    }
}
