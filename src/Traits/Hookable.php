<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Traits;

use AlizHarb\LaravelHooks\Facades\Hook;

/**
 * Trait Hookable
 *
 * Allows any class to easily dispatch hooks with instance context.
 */
trait Hookable
{
    /**
     * Dispatch an action with this instance as the first argument.
     *
     * @param string $tag The name of the action.
     * @param mixed ...$args Additional arguments.
     * @return void
     */
    protected function fireAction(string $tag, mixed ...$args): void
    {
        Hook::doAction($tag, $this, ...$args);
    }

    /**
     * Apply filter with this instance as the value.
     *
     * @param string $tag The name of the filter.
     * @param mixed ...$args Additional arguments.
     * @return mixed
     */
    protected function applyFilter(string $tag, mixed ...$args): mixed
    {
        return Hook::applyFilters($tag, $this, ...$args);
    }
}
