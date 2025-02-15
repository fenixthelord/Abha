<?php

namespace App\Observers;

use App\Jobs\Workflows\ProcessActionBasedWorkflow;
use App\Models\BaseModel;
use App\Models\Workflows\Workflow;

class BaseObserver
{
    public function created(BaseModel $model): void
    {
        $this->triggerWorkflow($model, 'data_creation');
    }

    public function updated(BaseModel $model)
    {
        $this->triggerWorkflow($model, 'field_change');
    }

    public function deleted(BaseModel $model)
    {
        $this->triggerWorkflow($model, 'data_deletion');
    }

    protected function triggerWorkflow(BaseModel $model, $actionType)
    {
        $workflows = Workflow::whereHas('blocks', function ($query) use ($actionType) {
            $query->where('type', 'start')
                ->where('config->trigger', 'action_based')
                ->where('config->action_type', $actionType);
        })->get();

        foreach ($workflows as $workflow) {
            ProcessActionBasedWorkflow::dispatch($workflow, [
                'model' => $model,
                'action_type' => $actionType,
            ]);
        }
    }
}
