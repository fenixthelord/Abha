<?php

namespace App\Models\Workflows;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Workflow extends BaseModel
{
    use HasTranslations, SoftDeletes;

    protected $fillable = ['name', 'description', 'start_block_id',   'end_block_id'];

    private $translatable = ['name', 'description'];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function blocks(): HasMany
    {
        return $this->hasMany(WorkflowBlock::class)->orderBy('order', 'asc');
    }

    public function scopeOrderByAll($query, $sortBy, $sortType)
    {
        if ($sortBy == 'name' && $sortType)
            $query->orderBy($sortBy, $sortType);
        else
            $query->orderBy('created_at', 'desc');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        });
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($workflow) {
            $workflow->blocks()->delete();
        });
    }
}
