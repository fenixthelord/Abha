<?php

namespace App\Http\Resources\Forms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormFieldResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $is_list = request()->route()->getName() === 'forms.list';
        return [
            'id' => $this->id,
            'order' => $this->order,
            'label' => $is_list ? $this->label : $this->getTranslations("label"),
            'placeholder' => $this->placeholder,
            'type' => $this->type,
            'required' => $this->required,
            'options' => FormFieldOptionResource::collection($this->whenLoaded('options')),
            'sources' => FormFieldDataSourceResource::collection($this->whenLoaded('sources')),
        ];
    }
}
