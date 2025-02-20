<?php

namespace App\Http\Resources\Customer;

use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'customer_id'        => $this->customer_id,
            'first_name'         => $this->first_name,
            'last_name'          => $this->last_name,
            'status'             => $this->status,
            'customer_type_id'   => $this->customer_type_id,
            'type_name' => Type::where('id', $this->type_id)->value('name') ?? 'Unknown',
            'form_submission_id' => $this->form_submission_id,
            'form_id'            => $this->form_id,
        ];
    }
}
