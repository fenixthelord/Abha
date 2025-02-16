<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Translatable\HasTranslations;

class Type extends BaseModel implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable, HasTranslations;

    protected $translatable = ['name'];
    protected $fillable = ['name'];
}
