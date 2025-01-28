<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;
use OwenIt\Auditing\Contracts\Auditable;

class Department extends Model implements Auditable
{
    use HasFactory, SoftDeletes, HasTranslations;
    use \OwenIt\Auditing\Auditable;


    private $translatable = ['name'];
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
