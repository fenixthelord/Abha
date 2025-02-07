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

    public $translatable = ['name', 'details'];

    protected $fillable = [
        'id',
        'department_id',
        'name',
        'details',
        'image',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
