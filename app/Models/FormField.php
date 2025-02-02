<?php

namespace App\Models;

use App\Enums\FormFiledType;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class FormField extends Model
{
    use HasTranslations;

    protected $fillable = [
        'form_id',
        'label',
        'type',
        'options',
        'required',
        'order'
    ];
    private $translatable = ['label'];

    protected $casts = [
        'type' => FormFiledType::class,
        'options' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
