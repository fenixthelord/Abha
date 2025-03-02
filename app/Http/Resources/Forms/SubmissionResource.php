<?php

namespace App\Http\Resources\Forms;

use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Http;

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
            'submitter_id' => $this->submitter_id,
            'submitter_service' => $this->submitter_service,
            'customer' => $this->nameCustomer(),
            'values' => SubmissionValueResource::collection($this->values),
        ];
    }

    public function nameCustomer()
    {
        if ($this->submitter_service == 'Customer' || $this->submitter_service == 'customer') {
            $customerService = new CustomerService();
            $response = $customerService->postCall('customer/show', ['id' => $this->submitter_id]);
            if (isset($response['error'])) {
                return 'Customer not found';
            } else {
                $customer = $response['data']['customer'];
                return ['name' => $customer['full_name'],
                    'image' => $customer['image']];
            }
        }
    }
}
