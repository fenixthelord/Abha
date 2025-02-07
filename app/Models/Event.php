<?php

namespace App\Models;

use App\Models\Forms\Form;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Event extends BaseModel
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $translatable = ['name', 'details'];
    protected $fillable = [
        'service_id',
        'form_id',
        'name',
        'details',
        'image',
        'start_date',
        'end_date',
        'file',
    ];

    protected $casts = ['name' => 'json', 'details' => 'json'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function forms(): MorphMany
    {
        return $this->morphMany(Form::class, 'formable');
    }

    public function scopeSearch($query, $search)
    {
        return $query
            ->where('name', 'LIKE', '%' . $search . '%')
            ->orWherE("details", "LIKE", "%" . $search . "%")
        ;
    }

    public function scopeFilter($query, $service_id)
    {
        return $query
            ->whereHas("service",  function ($query) use ($service_id) {
                $query->where("id", $service_id);
            });
    }
}
