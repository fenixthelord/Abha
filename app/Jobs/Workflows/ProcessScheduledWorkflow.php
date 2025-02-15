<?php

namespace App\Jobs\Workflows;

use App\Models\Workflows\Workflow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessScheduledWorkflow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $workflow;

    public function __construct(Workflow $workflow)
    {
        $this->workflow = $workflow;
    }

    public function handle()
    {
        $config = $this->workflow->blocks->where('type', 'start')->first()->config;

        if ($config['trigger'] === 'scheduled') {
            if ($config['scheduled_type'] === 'time_based') {
                $this->processTimeBasedWorkflow($config);
            } elseif ($config['scheduled_type'] === 'recurring') {
                $this->processRecurringWorkflow($config);
            }
        }
    }

    protected function processTimeBasedWorkflow($config)
    {
        // Logic for time-based workflows
        logger("Processing time-based workflow: " . $this->workflow->id);
    }

    protected function processRecurringWorkflow($config)
    {
        // Logic for recurring workflows
        logger("Processing recurring workflow: " . $this->workflow->id);
    }
}
