<?php

namespace App\Models\Forms;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormSubmissionValue extends BaseModel
{
    use SoftDeletes;
    protected $fillable = ['form_submission_id', 'form_field_id', 'value'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'form_field_id');
    }
}
