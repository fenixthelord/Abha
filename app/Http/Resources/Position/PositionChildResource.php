<?php

namespace App\Http\Resources\Position;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PositionChildResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "employees" => UserResource::collection($this->users)->each->onlyName(),
            "child_positions" => PositionResource::collection($this->children)
        ];
    }
}
