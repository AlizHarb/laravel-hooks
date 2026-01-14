<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use AlizHarb\LaravelHooks\Facades\Hook;

/**
 * Job to process hooks asynchronously.
 */
class ProcessHookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param string $hook
     * @param array $args
     */
    public function __construct(
        public string $hook,
        public array $args
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Hook::doAction($this->hook, ...$this->args);
    }
}
