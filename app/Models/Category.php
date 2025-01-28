<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable, HasTranslations;

    public $translatable = ['name'];

    protected $fillable = [
        "uuid",
        "name",
        "parent_id",
        "department_id",
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = \Str::uuid();
        });
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, "department_id");
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function deleteWithChildren()
    {
        $this->children->each->deleteWithChildren();

        $this->delete();
    }
}
