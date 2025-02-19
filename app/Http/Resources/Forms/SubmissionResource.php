<?php

namespace App\Http\Resources\Forms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'submitter_id' => $this->submitter_id ,
            'submitter_service' => $this->submitter_service ,
            'values' => SubmissionValueResource::collection($this->values) ,
        ];
    }
}
