<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Traits;

use AlizHarb\LaravelHooks\Facades\Hook;
use AlizHarb\LaravelHooks\PendingHookCall;

trait HasHooks
{
    /**
     * Trigger a model-specific action hook.
     *
     * @param string $hook
     * @param mixed ...$args
     * @return PendingHookCall
     */
    public function fieldAction(string $hook, mixed ...$args): PendingHookCall
    {
        return Hook::doAction($this->getModelHookName($hook), $this, ...$args);
    }

    /**
     * Apply model-specific filters.
     *
     * @param string $hook
     * @param mixed $value
     * @param mixed ...$args
     * @return mixed
     */
    public function fieldFilter(string $hook, mixed $value, mixed ...$args): mixed
    {
        return Hook::applyFilters($this->getModelHookName($hook), $value, $this, ...$args);
    }

    /**
     * Get the hook name prefixed with the model's short name.
     *
     * @param string $hook
     * @return string
     */
    protected function getModelHookName(string $hook): string
    {
        $shortName = strtolower(class_basename($this));

        return "model.{$shortName}.{$hook}";
    }
}
