<?php

namespace Hubiko\Hrm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnnouncementEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'announcement_id',
        'employee_id',
        'workspace',
        'created_by',
    ];
    
    protected static function newFactory()
    {
        return \Hubiko\Hrm\Database\factories\AnnouncementEmployeeFactory::new();
    }
}
