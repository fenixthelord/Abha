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
    protected static function boot()
    {
        parent::boot();
        static::bootHasDateTimeFields();
    }

    protected static function bootHasDateTimeFields()
    {
        static::registerModelEvent('booting', function ($model) {
            $model->initializeHasDateTimeFields();
        });
    }
}
