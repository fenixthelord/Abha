<?php

namespace App\Http\Resources\Type;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TypeResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request) {
        return [
            'id' => $this->id,
            'name' => $this->getTranslations('name'),
            'service_id' => $this->service?->id,
            'service_name' => $this->service?->getTranslations('name'),
            'image' => $this->image,
            'form_id' => $this->form?->id,
            'form_name' => $this->form?->getTranslations('name'),
        ];
    }
}
