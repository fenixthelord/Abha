<?php

namespace App\Models\Forms;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class FormType extends BaseModel
{
    use HasTranslations, SoftDeletes;

    protected $fillable = ['name'];
    private $translatable = ['name'];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function forms()
    {
        return $this->hasMany(Form::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($formType) {
            $formType->forms()->delete();
        });
    }
}
