<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Traits;

use AlizHarb\LaravelHooks\Facades\Hook;

/**
 * Trait MakesActionsHookable.
 *
 * Add this trait to your Service or Action classes to automatically
 * trigger hooks before and after execution.
 */
trait MakesActionsHookable
{
    /**
     * Hook name prefix for this class.
     */
    protected function getHookBaseName(): string
    {
        return 'action.'.str_replace('\\', '.', strtolower(static::class));
    }

    /**
     * Call the action with hooks and pipeline support.
     */
    public function executeWithHooks(callable $callback, mixed ...$args): mixed
    {
        $base = $this->getHookBaseName();

        // 1. Pre-execution Action
        Hook::doAction("{$base}.executing", $this, ...$args);

        // 2. Pipeline (Allows wrapping the entire execution)
        // Pipeline hooks should be filters that receive ($next, $action, ...$args)
        // and return the result of $next(...$args).
        $pipeline = Hook::applyFilters("{$base}.pipeline", function (...$args) use ($callback) {
            return $callback(...$args);
        }, $this, ...$args);

        try {
            $result = is_callable($pipeline) ? $pipeline(...$args) : $pipeline;

            // 3. Post-execution Action
            Hook::doAction("{$base}.executed", $this, $result, ...$args);

            // 4. Result Filtering
            return Hook::applyFilters("{$base}.result", $result, $this, ...$args);
        } catch (\Throwable $e) {
            Hook::doAction("{$base}.failed", $this, $e, ...$args);

            throw $e;
        }
    }
}
