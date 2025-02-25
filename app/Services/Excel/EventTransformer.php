<?php

namespace App\Services\Excel;

use App\Models\Event;

class EventTransformer
{
    public function transform(Event $event)
    {
        return [
            'Name (en)'       => $event->getTranslation('name', 'en'),
            'Name (ar)'       => $event->getTranslation('name', 'ar'),
            'start_date' => $event->start_date->format('Y-m-d'),
            'end_date' => $event->end_date->format('Y-m-d'),
            'Created At'      => $event->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
