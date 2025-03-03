<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Ticket extends BaseModel
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $translatable = ['name'];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'name', 'department_id', 'category_id', 'parent_id'];

    protected $casts = [
        'name' => 'array',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function parentTicket()
    {
        return $this->belongsTo(Ticket::class, 'parent_id');
    }
}
