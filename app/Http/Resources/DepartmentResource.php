<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'uuid' => $this->uuid,
      'name' => $this->name,
      'chields' => $this->whenLoaded('allChildren', function () {
        return CategoryResource::collection($this->children->load('children'));
      }),
      'chields' => $this->whenLoaded('categories', function () {
        return CategoryResource::collection($this->children);
      }),

    ];
  }
}
