<?php

namespace App\Http\Resources\Forms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionValueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'label' => $this->field->label ,
            'placeholder' => $this->field->placeholder ,
            'type' => $this->field->type ,
            'required' => $this->field->required ,
            'order' => $this->field->order ,
            'value' => $this->value ,
        ];
    }
}
