<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
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


    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($position) {
            if ($position->users()->count() > 0 || $position->children()->count() > 0) {
                throw new \Exception('Cannot delete position because it has associated users or sub positions.');
            }
        });
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function children(): HasMany
    {
        return $this->hasMany(Position::class, 'parent_id', 'id');
    }
    public function parent()
    {
        return $this->belongsTo(Position::class, 'parent_id');
    }
}
