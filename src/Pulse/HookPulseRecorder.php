<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Pulse;

use AlizHarb\LaravelHooks\Events\HookExecuted;
use Laravel\Pulse\Pulse;

class HookPulseRecorder
{
    /**
     * Create a new recorder instance.
     */
    public function __construct(
        protected Pulse $pulse,
    ) {
        //
    }
    /**
     * The events to listen for.
     *
     * @var array
     */
    public array $listen = [
        HookExecuted::class,
    ];

    /**
     * Record the hook execution.
     *
     * @param HookExecuted $event
     * @return void
     */
    public function record(HookExecuted $event): void
    {
        if (! config('hooks.pulse_enabled', false)) {
            return;
        }

        $this->pulse->record(
            type: 'hook',
            key: $event->hook,
            value: (int) ($event->duration * 1000),
            timestamp: now(),
        )->max();

        $this->pulse->record(
            type: 'hook_memory',
            key: $event->hook,
            value: $event->memory,
            timestamp: now(),
        )->avg();

        $this->pulse->record(
            type: 'hook_hits',
            key: $event->hook,
            timestamp: now(),
        )->count();
    }
}
