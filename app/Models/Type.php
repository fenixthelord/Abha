<?php

namespace App\Models;

use App\Models\Forms\Form;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Translatable\HasTranslations;

class Type extends BaseModel implements Auditable {
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable, HasTranslations;

    protected $translatable = ['name'];

    protected $fillable = [
        'name',
        'service_id',
        'form_id',
    ];

    public function service(): object
    {
        return $this->belongsTo(Service::class);
    }

    public function form(): object
    {
        return $this->belongsTo(Form::class);
    }
}
