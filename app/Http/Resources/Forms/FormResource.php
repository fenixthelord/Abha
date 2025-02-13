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
            'category_id' => $this->category_id,
            'category_name' => $this->whenLoaded('category', fn() => $this->category?->getTranslations('name')),
            'formable_id' => $this->formable_id,
            'formable_type' => $this->formable_type,
            'formable' => $this->whenLoaded('formable', function () use ($is_list) {
                if ($is_list) {
                    return $this->formable?->name;
                }

                // Check if the model has the 'getTranslations' method
                return method_exists($this->formable, 'getTranslations')
                    ? $this->formable->getTranslations('name')
                    : $this->formable?->name;
            }),

            'fields' => FormFieldResource::collection($this->whenLoaded('fields')),
        ];
    }
}
