<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks;

use BackedEnum;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Manage hooks for a specific temporary scope.
 */
class ScopedHookManager
{
    protected string $scopePrefix;

    /**
     * Create a new ScopedHookManager instance.
     */
    public function __construct(
        protected HookManager $manager,
        mixed $scope
    ) {
        $this->scopePrefix = $this->resolveScopePrefix($scope);
    }

    /**
     * Resolve the prefix for the given scope.
     */
    protected function resolveScopePrefix(mixed $scope): string
    {
        if ($scope instanceof Arrayable) {
            $scope = $scope->toArray();
        }

        if (is_object($scope)) {
            if (method_exists($scope, 'getKey')) {
                return get_class($scope).'::'.$scope->getKey();
            }

            return spl_object_hash($scope);
        }

        if (is_array($scope)) {
            return md5(json_encode($scope));
        }

        return (string) $scope;
    }

    /**
     * Get the scoped hook name.
     */
    protected function scopedHook(string|BackedEnum $hook): string
    {
        $hookName = $hook instanceof BackedEnum ? (string) $hook->value : $hook;

        return "scope::{$this->scopePrefix}::{$hookName}";
    }

    /**
     * Add an action to this scope.
     */
    public function addAction(string|BackedEnum $hook, callable|string|array $callback, int $priority = 10, int $acceptedArgs = 1): PendingHookRegistration
    {
        return $this->manager->addAction($this->scopedHook($hook), $callback, $priority, $acceptedArgs);
    }

    /**
     * Add a filter to this scope.
     */
    public function addFilter(string|BackedEnum $hook, callable|string|array $callback, int $priority = 10, int $acceptedArgs = 1): PendingHookRegistration
    {
        return $this->manager->addFilter($this->scopedHook($hook), $callback, $priority, $acceptedArgs);
    }

    /**
     * Execute an action within this scope.
     */
    public function doAction(string|BackedEnum $hook, mixed ...$args): PendingHookCall
    {
        return $this->manager->doAction($this->scopedHook($hook), ...$args);
    }

    /**
     * Apply filters within this scope.
     */
    public function applyFilters(string|BackedEnum $hook, mixed $value, mixed ...$args): mixed
    {
        return $this->manager->applyFilters($this->scopedHook($hook), $value, ...$args);
    }
}
