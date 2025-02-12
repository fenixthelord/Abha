<?php

namespace App\Models;
use App\Http\Traits\HasDateTimeFields;

class Job extends BaseModel
{
    use HasDateTimeFields;
    protected $fillable = [
        'id',
        'queue',
        'payload',
        'attempts',
        'reserved_at',
        'available_at',
        'created_at'
    ];
}
