<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Traits;

use AlizHarb\LaravelHooks\Bridge\FilamentHookBridge;
use Filament\Tables\Table;

trait InteractsWithHooks
{
    /**
     * Apply table hooks.
     *
     * @param mixed $table
     * @return mixed
     */
    protected function applyTableHooks(mixed $table): mixed
    {
        return FilamentHookBridge::applyTableHooks($table, $this->getHookContext());
    }

    /**
     * Apply schema hooks (formerly form).
     *
     * @param mixed $schema
     * @return mixed
     */
    protected function applySchemaHooks(mixed $schema): mixed
    {
        return FilamentHookBridge::applySchemaHooks($schema, $this->getHookContext());
    }

    /**
     * Apply infolist hooks.
     *
     * @param mixed $infolist
     * @return mixed
     */
    protected function applyInfolistHooks(mixed $infolist): mixed
    {
        return FilamentHookBridge::applyInfolistHooks($infolist, $this->getHookContext());
    }

    /**
     * Apply action hooks.
     *
     * @param mixed $action
     * @return mixed
     */
    protected function applyActionHooks(mixed $action): mixed
    {
        return FilamentHookBridge::applyActionHooks($action, $this->getHookContext());
    }

    /**
     * Get the context name for hooks.
     *
     * @return string
     */
    protected function getHookContext(): string
    {
        return class_basename($this);
    }
}
