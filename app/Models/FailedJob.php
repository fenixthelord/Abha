<?php

namespace App\Models;

class FailedJob extends BaseModel
{
    protected $fillable = [
        'id',
        'connection',
        'queue',
        'payload',
        'exception',
        'failed_at'
    ];

    protected $casts = [
        'failed_at' => 'datetime',
    ];
}
