<?php

namespace App\Jobs\Workflows;

use App\Models\Workflows\Workflow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessActionBasedWorkflow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $workflow;
    protected $eventData;

    public function __construct(Workflow $workflow, $eventData)
    {
        $this->workflow = $workflow;
        $this->eventData = $eventData;
    }

    public function handle()
    {
        $config = $this->workflow->blocks->where('type', 'start')->first()->config;

        if ($config['trigger'] === 'action_based') {
            switch ($config['action_type']) {
                case 'data_creation':
                    $this->processDataCreation($config);
                    break;
                case 'data_deletion':
                    $this->processDataDeletion($config);
                    break;
                case 'field_change':
                    $this->processFieldChange($config);
                    break;
            }
        }
    }

    protected function processDataCreation($config)
    {
        $model = $this->eventData['model'];
        $base_table = $config['base_table'];
        logger("Processing data creation workflow for model: " . get_class($model) . " Name: " . $model->name ?? $model->id);
        logger("Table: " . $model->getTable());
    }

    protected function processDataDeletion($config)
    {
        $model = $this->eventData['model'];
        $base_table = $config['base_table'];
        logger("Processing data deletion workflow for model: " . get_class($model) . " Name: " . $model->name ?? $model->id);
    }

    protected function processFieldChange($config)
    {
        $model = $this->eventData['model'];
        $base_field = $config['base_field'];
        $changed_fields = $this->eventData['changed_fields'];

        logger("Processing field change workflow for model: " . get_class($model) . " Name: " . $model->name ?? $model->id);
        foreach ($changed_fields as $key => $value) {
            if ($key === $base_field)
                logger("The '$key' field has been changed to '$value'.");
        }
    }
}
