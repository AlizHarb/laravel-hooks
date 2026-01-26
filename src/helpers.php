<?php

declare(strict_types=1);

use AlizHarb\LaravelHooks\Facades\Hook;

if (! function_exists('do_action')) {
    /**
     * Dispatch an action hook.
     *
     * @param string $hook
     * @param mixed ...$args
     * @return void
     */
    function do_action(string $hook, ...$args): void
    {
        Hook::doAction($hook, ...$args);
    }
}

if (! function_exists('apply_filters')) {
    /**
     * Apply filter hooks to a value.
     *
     * @param string $hook
     * @param mixed $value
     * @param mixed ...$args
     * @return mixed
     */
    function apply_filters(string $hook, $value, ...$args)
    {
        return Hook::applyFilters($hook, $value, ...$args);
    }
}

if (! function_exists('hook')) {
    /**
     * Fluent helper to interact with the hook system.
     *
     * @param mixed|null $scope
     * @return \AlizHarb\LaravelHooks\HookManager|\AlizHarb\LaravelHooks\ScopedHookManager
     */
    function hook($scope = null)
    {
        if ($scope) {
            return Hook::for($scope);
        }

        return app(\AlizHarb\LaravelHooks\HookManager::class);
    }
}
