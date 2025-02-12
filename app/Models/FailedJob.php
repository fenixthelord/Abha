<?php

namespace App\Models;

use App\Http\Traits\HasDateTimeFields;
class FailedJob extends BaseModel
{
    use HasDateTimeFields;
    protected $fillable = [
        'id',
        'connection',
        'queue',
        'payload',
        'exception',
        'failed_at'
    ];
}
