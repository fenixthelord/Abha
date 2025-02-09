<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditingTrait;
use Spatie\Translatable\HasTranslations;

class Service extends BaseModel implements Auditable
{
    use HasFactory, SoftDeletes, AuditingTrait, HasTranslations;

    protected $table = 'services';

    public $translatable = ['name', 'details'];

    protected $fillable = [
        'id',
        'department_id',
        'name',
        'details',
        'image',
    ];

    protected $casts =['id' => 'string'];

//    protected static function boot() {
//        parent::boot();
//        static::creating(function ($model) {
//            $model->id = Str::uuid();
//        });
//    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

}
