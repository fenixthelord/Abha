<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomPermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $action = explode(".",$this->name);
        return [
            'action' => ($action[1]) ? $action[1] : $this->name,
            'subject' => $this->group,
        ];
    }
}
