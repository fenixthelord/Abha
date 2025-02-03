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
            'label' => $this->label,
            'placeholder' => $this->placeholder,
            'type' => $this->type,
            // 'options' => json_decode($this->options, true),
            'options' => $this->options,
            'required' => $this->required,
            'order' => $this->order,
        ];
    }
}
