<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Http\Traits\HasDateTimeFields;

class BaseModel extends Model
{
    use HasDateTimeFields;
    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
        });
        static::bootHasDateTimeFields();
    }
    protected static function bootHasDateTimeFields()
    {
        static::registerModelEvent('booting', function ($model) {
            $model->initializeHasDateTimeFields();
        });
    }
}
