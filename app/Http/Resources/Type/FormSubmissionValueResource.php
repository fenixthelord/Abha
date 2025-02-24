<?php

namespace App\Http\Resources\Type;

use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormSubmissionValueResource extends JsonResource {

    public function toArray(Request $request): array
    {
        return [
            'form_submission_value' => $this->id,
            'field_id'     => $this->field->id ?? null,
            'value'        => $this->value ?? null,
        ];
    }
}

