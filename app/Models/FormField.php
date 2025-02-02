<?php

namespace App\Models;

use App\Enums\FormFiledType;
use Spatie\Translatable\HasTranslations;

class FormField extends BaseModel
{
    use HasTranslations;

    protected $fillable = [
        'form_id',
        'label',
        'placeholder',
        'type',
        'options',
        'required',
        'order'
    ];
    private $translatable = ['label', 'placeholder'];

    protected $casts = [
        'type' => FormFiledType::class,
        'options' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
