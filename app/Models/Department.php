<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Translatable\HasTranslations;

class Department extends BaseModel implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable, HasTranslations;

    private $translatable = ['name'];
    protected $fillable = ['name'];

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

    public function employees()
    {
        return $this->hasMany(User::class, "department_id");
    }
}
