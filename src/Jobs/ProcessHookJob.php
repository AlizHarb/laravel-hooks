<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Jobs;

use AlizHarb\LaravelHooks\Facades\Hook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Job to process hooks asynchronously.
 */
class ProcessHookJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $hook,
        public array $args
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Hook::doAction($this->hook, ...$this->args);
    }
}
