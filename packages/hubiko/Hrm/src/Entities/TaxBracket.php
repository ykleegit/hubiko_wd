<?php

namespace Hubiko\Hrm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxBracket extends Model
{
    use HasFactory;

    protected $fillable = [
        'from',
        'to',
        'fixed_amount',
        'percentage',
        'workspace',
        'created_by',
    ];
    
    protected static function newFactory()
    {
        return \Hubiko\Hrm\Database\factories\TaxBracketFactory::new();
    }
}
