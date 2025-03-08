<?php

namespace App\Jobs\Workflows;

use App\Mail\SystemErrorMail;
use App\Models\Workflows\Workflow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ProcessSystemEventWorkflow implements ShouldQueue
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

        if ($config['trigger'] === 'system_event') {
            switch ($config['event_type']) {
                case 'system_error':
                    $this->processSystemError($config);
                    break;
                case 'database_connection_failed':
                    $this->processSystemError($config);
                    break;
                case 'disk_space_low':
                    $this->processSystemError($config);
                    break;
            }
        }
    }

    protected function processSystemError($config)
    {
        logger("Processing system event workflow: " . $this->workflow->id, [
            'message' => $this->eventData['message'],
            'context' => $this->eventData['context'],
        ]);

        if (isset($this->workflow->config['notify_email'])) {
            $email = $this->workflow->config['notify_email'];
            Mail::to($email)->send(new SystemErrorMail(
                $this->eventData['message'],
                $this->eventData['context']
            ));
        }
    }
}
