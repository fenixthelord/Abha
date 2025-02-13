<?php

namespace App\Listeners\Workflows;

use App\Jobs\Workflows\ProcessSystemEventWorkflow;
use App\Models\Workflows\Workflow;
use Illuminate\Log\Events\MessageLogged;

class HandleSystemErrorListener
{
    public function handle(MessageLogged $event)
    {
        if ($event->level === 'error') {
            $workflows = Workflow::whereHas('blocks', function ($query) {
                $query->where('type', 'start')
                    ->where('config->trigger', 'system_event')
                    ->where('config->event_type', 'system_error');
            })->get();

            $eventData = [
                'message' => $event->message,
                'context' => null,
                // 'context' => $event->context,
            ];
            foreach ($workflows as $workflow) {
                ProcessSystemEventWorkflow::dispatch($workflow, $eventData);
            }
        }
    }
}
