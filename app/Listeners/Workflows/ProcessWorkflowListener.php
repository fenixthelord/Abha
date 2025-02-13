<?php

namespace App\Listeners\Workflows;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessWorkflowListener
{

    public function __construct() {}

    public function handle(object $event): void {}
}
