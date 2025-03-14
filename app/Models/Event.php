<?php

namespace App\Models;

use App\Models\Forms\Form;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Translatable\HasTranslations;

class Event extends BaseModel implements Auditable
{
    use HasFactory, SoftDeletes, HasTranslations;
    use \OwenIt\Auditing\Auditable;

    protected $translatable = ['name', 'details'];
    protected $fillable = [
        'service_id',
        'name',
        'details',
        'image',
        'start_date',
        'end_date',
        'file',
        'customer_type_id',
    ];

    protected $casts = [
        'name' => 'json',
        'details' => 'json',
        'service_id' => 'string',
        'form_id' => 'string',
        'file' => 'string',
        'image' => 'string',];
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
