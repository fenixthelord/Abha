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
