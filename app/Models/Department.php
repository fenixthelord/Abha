<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Translatable\HasTranslations;

class Department extends BaseModel implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable, HasTranslations;

    private $translatable = ['name'];
    protected $fillable = ['name'];
    protected $casts = ['name' => 'json'];

    public function categories(): HasMany
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

    public function employees(): HasMany
    {
        return $this->hasMany(User::class, "department_id");
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
