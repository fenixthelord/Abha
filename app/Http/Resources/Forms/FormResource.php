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
            'name' => $this->getTranslations('name'),
            'type' => $this->type?->name,
            'fields' => FormFieldResource::collection($this->whenLoaded('fields')),
        ];
    }
}
