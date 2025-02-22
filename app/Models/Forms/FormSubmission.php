<?php

namespace App\Models\Forms;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormSubmission extends BaseModel
{
    use SoftDeletes;
    protected $fillable = ['form_id', 'submitter_id', 'submitter_service'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(FormSubmissionValue::class, 'form_submission_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($form_submission) {
            $form_submission->values()->delete();
        });
    }
}
