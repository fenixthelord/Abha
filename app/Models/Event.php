<?php

namespace App\Models;

use App\Models\Forms\Form;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use App\Http\Traits\HasDateTimeFields;

class Event extends BaseModel
{
    use HasFactory, SoftDeletes, HasTranslations, HasDateTimeFields;

    protected $translatable = ['name', 'details'];
    protected $fillable = [
        'service_id',
        'name',
        'details',
        'image',
        'start_date',
        'end_date',
        'file',
    ];

    protected $casts = [
        'name' => 'json',
        'details' => 'json',
        'service_id' => 'string',
        'form_id' => 'string',
        'file' => 'string',
        'image' => 'string',];
    protected static function boot()
    {
        parent::boot();
        static::bootHasDateTimeFields();
    }

    protected static function bootHasDateTimeFields()
    {
        static::registerModelEvent('booting', function ($model) {
            $model->initializeHasDateTimeFields();
        });
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
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
