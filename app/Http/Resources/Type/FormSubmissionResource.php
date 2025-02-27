<?php

namespace App\Http\Resources\Type;

use App\Http\Resources\Forms\SubmissionValueResource;
use App\Models\Event;
use App\Models\Forms\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormSubmissionResource extends JsonResource {

    public function toArray(Request $request): array {
        $event_id =  $this->form?->type?->form_index;
        if ($event_id) {
            $event = Event::findOrFail($event_id);
        }

        return [
            'form_submission_id' => $this->id,
            'form' => $this->form ? [
                'form_id' => $this->form->id ?? null,
                'form_name' => $this->form ? $this->form->getTranslations('name') : null,
                'form_type' => $this->form->type?->name ?? 'Unknown Type',
                'event_id' => $event_id,
                'event_name' => $event ? $event->getTranslations('name') : 'No Event',
            ] : null,
            'status' => $this->status ?? 'pending',
            'values' => SubmissionValueResource::collection($this->values),
        ];
    }
}
