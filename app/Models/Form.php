<?php

namespace App\Models;

use Spatie\Translatable\HasTranslations;

class Form extends BaseModel
{
    use HasTranslations;

    protected $fillable = ['category_id', 'name'];
    private $translatable = ['name'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function fields()
    {
        return $this->hasMany(FormField::class);
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }
}
