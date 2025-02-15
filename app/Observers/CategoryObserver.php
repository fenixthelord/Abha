<?php

namespace App\Observers;

use App\Jobs\Workflows\ProcessActionBasedWorkflow;
use App\Models\Category;
use App\Models\Workflows\Workflow;

class CategoryObserver
{
    public function created(Category $category): void
    {
        $this->triggerWorkflow($category, 'data_creation');
    }

    public function updated(Category $category)
    {
        $changedFields = $category->getDirty();
        $this->triggerWorkflow($category, 'field_change', $changedFields);
    }

    public function deleted(Category $category)
    {
        $this->triggerWorkflow($category, 'data_deletion');
    }

    protected function triggerWorkflow(Category $category, $actionType, $changedFields = null)
    {
        $workflows = Workflow::whereHas('blocks', function ($query) use ($actionType) {
            $query->where('type', 'start')
                ->where('config->trigger', 'action_based')
                ->where('config->base_table', 'categories')
                ->where('config->action_type', $actionType);
        })->get();

        foreach ($workflows as $workflow) {
            ProcessActionBasedWorkflow::dispatch($workflow, [
                'model' => $category,
                'action_type' => $actionType,
                'changed_fields' => $changedFields,
            ]);
        }
    }
}
