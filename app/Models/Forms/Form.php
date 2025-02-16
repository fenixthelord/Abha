<?php

namespace App\Models\Forms;

use App\Models\BaseModel;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Form extends BaseModel
{
    use HasTranslations, SoftDeletes;

    protected $fillable = ['formable_id', 'formable_type', 'name'];
    private $translatable = ['name'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function formable(): MorphTo
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
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhereHasMorph(
                        'formable',
                        [Category::class, Event::class],
                        function ($query, $type) use ($search) {
                            if ($type === Category::class) {
                                $query->where('name', 'like', '%' . $search . '%');
                            } elseif ($type === Event::class) {
                                $query->where('name', 'like', '%' . $search . '%');
                            }
                        }
                    );
            });
        })->when($filters['event_id'] ?? null, function ($query, $eventId) {
            $query->whereHasMorph('formable', [Event::class], function ($query) use ($eventId) {
                $query->where('id', $eventId);
            });
        })->when($filters['category_id'] ?? null, function ($query, $categoryId) {
            $query->whereHasMorph('formable', [Category::class], function ($query) use ($categoryId) {
                $query->where('id', $categoryId);
            });
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
