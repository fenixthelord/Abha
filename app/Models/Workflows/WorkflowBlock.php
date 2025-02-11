<?php

namespace App\Models\Workflows;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowBlock extends BaseModel
{
    use SoftDeletes;
    protected $fillable = ['workflow_id', 'type', 'config'];

    protected $casts = ['config' => 'array'];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }
}
