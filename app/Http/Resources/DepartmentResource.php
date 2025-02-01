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
      'department_uuid' => $this->uuid,
      'name' =>[
          'name' => $this->getTranslations("name"),
      ],
      'chields' => $this->whenLoaded('categories', function () {
        return CategoryResource::collection($this->categories()->where("parent_id", null)->get()->load("children"));
      }),

      // 'categories' => $this->whenLoaded('categories', function () {
      //   return CategoryResource::collection($this->children);
      // }),

    ];
  }
}
