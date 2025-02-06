<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditingTrait;
use Spatie\Translatable\HasTranslations;

class Service extends Model implements Auditable {
    use HasFactory, SoftDeletes, AuditingTrait, HasTranslations;

    protected $table = 'services'; protected $primaryKey = 'id'; public $incrementing = false; protected $keyType = 'string';


    public $translatable = ['name', 'details'];

    protected $fillable = [
        'id',
        'name',
        'details',
        'image',
        'department_id',
    ];

    protected static function boot() {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function services () 
    {
        return $this->hasMany(Service::class);
    }
}
