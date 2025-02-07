<?php

namespace App\Http\Resources\Forms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        $is_list = request()->route()->getName() === 'forms.list';
        return [
            'id' => $this->id,
            'name' => $this->name,
            'formable_id' => $this->formable_id,
            'formable_type' => $this->formable_type,
            'formable' => $this->whenLoaded('formable', fn() => $is_list ? $this->formable?->name : $this->formable?->getTranslations('name')),

            'fields' => FormFieldResource::collection($this->whenLoaded('fields')),
        ];
    }
}
