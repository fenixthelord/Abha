<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Translatable\HasTranslations;

class Position extends BaseModel implements Auditable
{
    use HasFactory, SoftDeletes, HasTranslations;
    use \OwenIt\Auditing\Auditable;
    const MASTER_ID = 'ad02d43b-0e34-4689-885f-b0958c9c900c';

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

    /**
     * Return all children ids "Recursively"
     * @param $Id
     * @return array
     * @throws Exception
     */
    public static function getChildrenIds($id): array
    {
        $position = Position::find($id);

        if (!$position) {
            throw new Exception("Position not found");
        }

        $childrenIds = [$position->id];

        foreach ($position->children as $child) {
            $childrenIds = array_merge($childrenIds, static::getChildrenIds($child->id));
        }

        return $childrenIds;
    }
}
