<?php

namespace App\Events\Workflows;

use App\Models\Workflows\Workflow;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkflowTriggered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $workflow;
    public $triggerType;
    public $eventData;

    public function __construct(Workflow $workflow, $triggerType, $eventData = [])
    {
        $this->workflow = $workflow;
        $this->triggerType = $triggerType;
        $this->eventData = $eventData;
    }
}
