<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Events;

use Illuminate\Foundation\Events\Dispatchable;

class HookExecuted
{
    use Dispatchable;

    public function __construct(
        public string $hook,
        public mixed $value,
        public array $args,
        public float $duration,
        public int $memory
    ) {
    }
}
