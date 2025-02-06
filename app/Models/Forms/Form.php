<?php

namespace App\Models\Forms;

use App\Models\BaseModel;
use App\Models\Category;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Form extends BaseModel
{
    use HasTranslations, SoftDeletes;

    protected $fillable = ['category_id', 'name'];
    private $translatable = ['name'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function formable()
    {
        return $this->morphTo();
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('order', 'asc');
    }

    public function submissions(): HasMany
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

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($form) {
            $form->fields()->delete();
            $form->submissions()->delete();
        });
    }
}
