<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Form extends BaseModel
{
    use HasTranslations, SoftDeletes;

    protected $fillable = ['category_id', 'name'];
    private $translatable = ['name'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function fields()
    {
        return $this->hasMany(FormField::class);
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function scopeOrderByAll($query, $sortBy, $sortType)
    {
        if ($sortBy == 'name' && $sortType)
            $query->orderBy($sortBy, $sortType);
        else
            $query->orderBy('created_at', 'desc');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })->orWhereHas('category', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });;
        });
    }
}
