<?php

namespace App\Http\Resources\Forms;

use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'category_name' => $this->category->name,
            'fields' => FormFieldResource::collection($this->whenLoaded('fields')),
        ];
    }
}
