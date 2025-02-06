<?php

namespace App\Models;

use App\Models\Forms\Form;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Translatable\HasTranslations;
use Illuminate\Support\Str;


class Event extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable, HasTranslations;

    protected $translatable = [
        'name',
        'details',
    ];
    protected $fillable = [
        'id',
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
        'id' => 'string',
        'name' => 'json',
        'details' => 'json',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function form () {
        return $this->belongsTo(Form::class);
        
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
