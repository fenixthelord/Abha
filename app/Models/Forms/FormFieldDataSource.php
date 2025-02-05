<?php

namespace App\Models\Forms;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormFieldDataSource extends BaseModel
{
    use SoftDeletes;
    protected $fillable = ['form_field_id', 'source_table', 'source_column'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function field(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
