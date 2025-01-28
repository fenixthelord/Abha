<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Translatable\HasTranslations;

class Department extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable, HasTranslations;

    public $translatable = ['name'];
    protected $fillable = [
        'name',
        'uuid',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }

    public function categories()
    {
        return $this->hasMany(Category::class, "department_id");
    }

    public function allChildren()
    {
        return $this->categories()->with('allChildren');
    }

    public function deleteWithChildren()
    {
        $this->categories->each(function ($category) {
            $category->deleteWithChildren();
        });
    }
}
