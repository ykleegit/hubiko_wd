<?php

namespace Hubiko\Hrm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_required',
        'workspace',
        'created_by',
    ];
    
    protected static function newFactory()
    {
        return \Hubiko\Hrm\Database\factories\DocumentTypeFactory::new();
    }
}
