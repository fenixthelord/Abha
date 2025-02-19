<?php

namespace App\Http\Resources\Forms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormSubmissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'form_id' => $this->id,
            'form_name' => $this->name,
            'form_type' => $this->type?->name,
            'submissions' =>SubmissionResource::collection($this->submissions),
        ];
    }
}
