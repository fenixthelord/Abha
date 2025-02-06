<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditingTrait;
use phpseclib3\Common\Functions\Strings;
use Spatie\Translatable\HasTranslations;

class Service extends Model implements Auditable {
    use HasFactory, SoftDeletes, AuditingTrait, HasTranslations;

    protected $table = 'services';

    public $translatable = ['name', 'details'];

    protected $fillable = [
        'id',
        'name',
        'details',
        'image',
        'department_id',
    ];

    protected $casts =['id' => 'String'];

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
