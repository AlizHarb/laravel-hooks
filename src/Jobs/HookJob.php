<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Jobs;

use AlizHarb\LaravelHooks\Facades\Hook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HookJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param string $hook The hook name
     * @param mixed $callback The listener callback (string or array, no closures)
     * @param array $args The arguments to pass to the listener
     */
    public function __construct(
        protected string $hook,
        protected mixed $callback,
        protected array $args
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Execute the specific listener directly without re-triggering the whole hook
        if (is_string($this->callback) || is_array($this->callback)) {
            call_user_func($this->callback, ...$this->args);
        }
    }
}
