<?php

namespace Hubiko\LandingPage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JoinUs extends Model
{
    use HasFactory;

    protected $table = 'join_us';

    protected $fillable = ['email'];

    protected static function newFactory()
    {
        return \Hubiko\LandingPage\Database\factories\JoinUsFactory::new();
    }
}
