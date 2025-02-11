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
        return $this->hasMany(WorkflowBlock::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($workflow) {
            $workflow->blocks()->delete();
        });
    }
}
