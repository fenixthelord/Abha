<?php

namespace App\Models\Forms;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormSubmission extends BaseModel
{
    use SoftDeletes;
    protected $fillable = ['form_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function values()
    {
        return $this->hasMany(FormSubmissionValue::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($form_submission) {
            $form_submission->values()->delete();
        });
    }
}
