<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormSubmissionValue extends Model
{
    protected $fillable = ['form_submission_id', 'form_field_id', 'value'];

    public function submission()
    {
        return $this->belongsTo(FormSubmission::class);
    }

    public function field()
    {
        return $this->belongsTo(FormField::class);
    }
}
