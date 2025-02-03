<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormSubmissionValue extends BaseModel
{
    use SoftDeletes;
    protected $fillable = ['form_submission_id', 'form_field_id', 'value'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function submission()
    {
        return $this->belongsTo(FormSubmission::class);
    }

    public function field()
    {
        return $this->belongsTo(FormField::class);
    }
}
