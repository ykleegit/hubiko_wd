<?php

namespace Hubiko\Ticket\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Priority extends Model
{
    use HasFactory;

    protected $table = 'ticket_priorities';

    protected $fillable = [
        'name',
        'color',
        'created_by',
        'workspace'
    ];

    // Scopes for workspace and user filtering
    public function scopeWorkspace($query, $workspace = null)
    {
        $workspace = $workspace ?? getActiveWorkSpace();
        return $query->where('workspace', $workspace);
    }

    public function scopeCreatedBy($query, $createdBy = null)
    {
        $createdBy = $createdBy ?? creatorId();
        return $query->where('created_by', $createdBy);
    }
    
    public function tickets()
    {
        return $this->hasMany('Hubiko\Ticket\Entities\Ticket', 'priority', 'id');
    }
} 