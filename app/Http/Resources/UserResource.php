<?php

namespace App\Http\Resources;

use App\Http\Resources\Permissions\NewPermissionsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->first_name . ' ' . $this->last_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            $this->mergeWhen(!$this->merge, [
                "department_id" => $this->department?->id,
                "department_name" => $this->department?->getTranslations("name"),
                'email' => $this->email,
                'phone' => $this->phone,
                'gender' => $this->gender,
                'image' => $this->image,
                'alt' => $this->alt,
                'job' => $this->job,
                'job_id' => $this->job_id,
                'active' => $this->active,
                'role' => $this->role,
                "user_role" => $this->getRoleNames(),
                "permission" => new NewPermissionsResource($this->getAllPermissions()),
                'position_name' => [
                    'en' => $this->position?->getTranslation('name', 'en'),
                    'ar' => $this->position?->getTranslation('name', 'ar'),
                ],
            ]),
        ];
    }
    public function onlyName()
    {
        $this->merge = true;

        return $this;
    }
}
