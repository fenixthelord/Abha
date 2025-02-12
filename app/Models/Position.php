<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Position extends BaseModel
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $translatable = ['name'];

    protected $fillable = [
        "name",
        "parent_id"
    ];
}
