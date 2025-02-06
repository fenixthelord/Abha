<?php

namespace App\Http\Resources\Forms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormFieldResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order' => $this->order,
            'label' => $this->label,
            'placeholder' => $this->placeholder,
            'type' => $this->type,
            'required' => $this->required,
            'options' => FormFieldOptionResource::collection($this->whenLoaded('options')),
            'sources' => FormFieldDataSourceResource::collection($this->whenLoaded('sources')),
        ];
    }
}
