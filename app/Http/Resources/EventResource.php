<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'service_id' => $this->service_id,
            'service_name' => $this->service?->getTranslations("name"),
            'name' => $this->getTranslations("name"),
            'details' => $this->getTranslations("details"),
            // $this->mergeWhen($this->info, [
            'file' => $this->file,
            'image' => $this->image,
            'start_date' => $this->start_date,
            'start_date_hijri' => $this->hijri['start_date_hijri'],
            'end_date_hijri' => $this->hijri['end_date_hijri'],
            'end_date' => $this->end_date
        ];
    }

    public function allInfo()
    {
        $this->info = true;
        return $this;
    }
}
