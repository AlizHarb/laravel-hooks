<?php

declare(strict_types=1);

use AlizHarb\LaravelHooks\Facades\Hook;

if (! function_exists('add_action')) {
    /**
     * Hooks a function on to a specific action.
     *
     * @param string $hook
     * @param callable|string|array $callback
     * @param int $priority
     * @param int $acceptedArgs
     * @return void
     */
    function add_action(string $hook, callable|string|array $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        Hook::addAction($hook, $callback, $priority, $acceptedArgs);
    }
}

if (! function_exists('do_action')) {
    /**
     * Execute functions hooked on a specific action hook.
     *
     * @param string $hook
     * @param mixed ...$args
     * @return void
     */
    function do_action(string $hook, mixed ...$args): void
    {
        Hook::doAction($hook, ...$args);
    }
}

if (! function_exists('add_filter')) {
    /**
     * Hooks a function or method to a specific filter action.
     *
     * @param string $hook
     * @param callable|string|array $callback
     * @param int $priority
     * @param int $acceptedArgs
     * @return void
     */
    function add_filter(string $hook, callable|string|array $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        Hook::addFilter($hook, $callback, $priority, $acceptedArgs);
    }
}

if (! function_exists('apply_filters')) {
    /**
     * Call the functions added to a filter hook.
     *
     * @param string $hook
     * @param mixed $value
     * @param mixed ...$args
     * @return mixed
     */
    function apply_filters(string $hook, mixed $value, mixed ...$args): mixed
    {
        return Hook::applyFilters($hook, $value, ...$args);
    }
}
