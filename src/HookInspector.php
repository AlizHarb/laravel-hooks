<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks;

/**
 * Record hook execution history for debugging.
 */
class HookInspector
{
    /** @var array<int, array> */
    protected array $history = [];

    protected bool $enabled;

    /**
     * Create a new HookInspector instance.
     */
    public function __construct()
    {
        $this->enabled = config('hooks.debug', false);
    }

    /**
     * Record a hook execution.
     *
     * @param string $hook
     * @param mixed $value
     * @param array $args
     * @return void
     */
    public function record(string $hook, mixed $value, array $args): void
    {
        if (! $this->enabled) {
            return;
        }

        $this->history[] = [
            'hook' => $hook,
            'value' => $value,
            'args' => $args,
            'microtime' => microtime(true),
            'memory' => memory_get_usage(),
        ];
    }

    /**
     * Get the execution history.
     *
     * @return array
     */
    public function getHistory(): array
    {
        return $this->history;
    }
}
