<?php

namespace App\Models\Forms;

use App\Enums\FormFiledType;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class FormField extends BaseModel
{
    use HasTranslations, SoftDeletes;

    protected $fillable = ['form_id', 'label', 'placeholder', 'type', 'required', 'order'];
    private $translatable = ['label', 'placeholder'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'type' => FormFiledType::class,
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(FormFieldOption::class)->orderBy('order', 'asc');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(FormFieldDataSource::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($form_field) {
            $form_field->options()->delete();
        });
    }
}
