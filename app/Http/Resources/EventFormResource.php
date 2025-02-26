<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Forms\Form;
use App\Models\Forms\FormType;
use App\Http\Resources\Forms\FormResource;
use App\Http\Traits\HasDateTimeFields;


class EventFormResource extends JsonResource
{
    use HasDateTimeFields;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        // Fetch the form type related to this event
        $form_type = FormType::where('form_index', $this->id)->first();

        // If form type is found, retrieve the form associated with it
        $form = $form_type ? Form::with(['fields.options', 'fields.sources'])->where('form_type_id', $form_type->id)->first() : null;

        return [
            'id' => $this->id,
            'service_id' => $this->service_id,
            'service_name' => $this->service?->getTranslations("name"),
            'name' => $this->getTranslations("name"),
            'details' => $this->getTranslations("details"),
            // $this->mergeWhen($this->info, [
            'file' => $this->file,
            'image' => $this->image,
            'start_date' => $this->formatDateTime($this->start_date),
            'end_date' => $this->formatDateTime($this->end_date),
            'start_date_hijri' => $this->hijri['start_date_hijri'],
            'end_date_hijri' => $this->hijri['end_date_hijri'],
            'form' => $form ? new FormResource($form) : null,  // Include the form in the response
        ];
    }

}
