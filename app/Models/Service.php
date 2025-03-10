<?php

namespace App\Models;

use Google\Service\MyBusinessBusinessInformation\ServiceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditingTrait;
use Spatie\Translatable\HasTranslations;

class Service extends BaseModel implements Auditable {
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

    protected $casts =[
        'id' => 'string',
        'department_id' => 'string',
        'name' => 'json',
        'details' => 'json',
        'image' => 'string'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function types() {
        return $this->hasMany(Type::class);
    }
}
