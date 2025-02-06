<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditingTrait;
use Spatie\Translatable\HasTranslations;

class Service extends BaseModel implements Auditable
{
    use HasFactory, SoftDeletes, AuditingTrait, HasTranslations;


    public $translatable = ['name', 'details'];

    protected $fillable = [
        'id',
        'name',
        'details',
        'image',
        'department_id',
    ];


    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
