<?php

namespace App\Models\Forms;

use App\Models\BaseModel;
use App\Models\Category;
use App\Models\Event;
use App\Models\Type;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Form extends BaseModel
{
    use HasTranslations, SoftDeletes;

    protected $fillable = ['name','form_type_id'];
    private $translatable = ['name'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];



    public function type(): BelongsTo
    {
        return $this->belongsTo(FormType::class, 'form_type_id');
    }
    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('order', 'asc');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function event()
    {
        return $this->hasOneThrough(Event::class, FormType::class, 'id', 'id', 'form_type_id', 'form_index');
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
                    ->orWhereHas(
                        'type', function ($query) use ($search) {
                            return $query->where('name', 'like', '%' . $search . '%');
                        }
                    );
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
    public function types() {
        return $this->hasOne(Type::class);
    }
}
