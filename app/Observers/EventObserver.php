<?php

namespace App\Observers;

use App\Jobs\Workflows\ProcessActionBasedWorkflow;
use App\Models\Event;
use App\Models\Workflows\Workflow;

class EventObserver
{
    public function created(Event $event): void
    {
        $this->triggerWorkflow($event, 'data_creation');
    }

    public function updated(Event $event)
    {
        $changedFields = $event->getDirty();
        $this->triggerWorkflow($event, 'field_change', $changedFields);
    }

    public function deleted(Event $event)
    {
        $this->triggerWorkflow($event, 'data_deletion');
    }

    protected function triggerWorkflow(Event $event, $actionType, $changedFields = null)
    {
        $workflows = Workflow::whereHas('blocks', function ($query) use ($actionType) {
            $query->where('type', 'start')
                ->where('config->trigger', 'action_based')
                ->where('config->base_table', 'events')
                ->where('config->action_type', $actionType);
        })->get();

        foreach ($workflows as $workflow) {
            ProcessActionBasedWorkflow::dispatch($workflow, [
                'model' => $event,
                'action_type' => $actionType,
                'changed_fields' => $changedFields,
            ]);
        }
    }
}
