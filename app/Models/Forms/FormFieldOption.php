<?php

namespace App\Models\Forms;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class FormFieldOption extends BaseModel
{
    use HasTranslations, SoftDeletes;
    protected $fillable = ['form_field_id', 'label', 'selected',  'order'];
    private $translatable = ['label'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function field()
    {
        return $this->belongsTo(Form::class);
    }
}
