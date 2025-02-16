<?php

namespace App\Console;

use App\Jobs\Workflows\ProcessScheduledWorkflow;
use App\Models\Workflows\Workflow;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $workflows = Workflow::whereHas('blocks', function ($query) {
            $query->where('type', 'start')
                ->where('config->trigger', 'scheduled')
                ->where('config->scheduled_type', 'recurring');
        })->get();

        foreach ($workflows as $workflow) {
            $config = $workflow->blocks->where('type', 'start')->first()->config;

            if ($config['recurrence'] === 'daily') {
                $schedule->job(new ProcessScheduledWorkflow($workflow))
                    ->dailyAt($config['start_time']);
            } elseif ($config['recurrence'] === 'weekly') {
                $schedule->job(new ProcessScheduledWorkflow($workflow))
                    ->weeklyOn($config['weekday'], $config['start_time']);
            } elseif ($config['recurrence'] === 'monthly') {
                $schedule->job(new ProcessScheduledWorkflow($workflow))
                    ->monthlyOn($config['day_of_month'], $config['start_time']);
            } elseif ($config['recurrence'] === 'annual') {
                $schedule->job(new ProcessScheduledWorkflow($workflow))
                    ->yearlyOn($config['month'], $config['day'], $config['start_time']);
            }
        }
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
