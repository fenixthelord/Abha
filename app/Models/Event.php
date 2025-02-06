<?php

namespace App\Models;

use App\Models\Forms\Form;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Event extends BaseModel
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $translatable = ['name', 'details'];
    protected $fillable = [
        'service_id',
        'name',
        'details',
        'form_id',
        'file',
        'start_date',
        'end_date',
        'image'
    ];

    protected $casts = [
        'name' => 'json',
        'details' => 'json',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function forms()
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
