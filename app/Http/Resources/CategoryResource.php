<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // dd($this->children);

        return [

            $this->mergeWhen($this->isDeparted, [
                "department_uuid" => $this->department?->uuid,
                "department_name" => $this->department?->getTranslations('name'),
            ]),

            'category_uuid' => $this->uuid,
            'name' => $this->getTranslations('name'),
            'chields' => $this->whenLoaded('children', function () {
                return CategoryResource::collection($this->children->load('children'));
            }),
        ];
    }

    public function withDeparted()
    {
        $this->isDeparted = true;
        return $this;
    }

    // public static function collection($resource)
    // {
    //     return tap(parent::collection($resource), function ($collection) {
    //         if (request()->has('with_departed')) {
    //             $collection->each->withDeparted();
    //         }
    //     });
    // }
}
