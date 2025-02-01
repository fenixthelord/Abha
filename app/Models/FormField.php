<?php

namespace App\Models;

use App\Enums\FormFiledType;
use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    protected $fillable = [
        'form_id',
        'label',
        'type',
        'options',
        'required',
        'order'
    ];

    protected $casts = [
        'type' => FormFiledType::class,
        'options' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
