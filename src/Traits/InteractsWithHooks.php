<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Traits;

use AlizHarb\LaravelHooks\Bridge\FilamentHookBridge;
use Filament\Tables\Table;

trait InteractsWithHooks
{
    /**
     * Apply table hooks.
     */
    protected function applyTableHooks(mixed $table): mixed
    {
        return FilamentHookBridge::applyTableHooks($table, $this->getHookContext());
    }

    /**
     * Apply schema hooks (formerly form).
     */
    protected function applySchemaHooks(mixed $schema): mixed
    {
        return FilamentHookBridge::applySchemaHooks($schema, $this->getHookContext());
    }

    /**
     * Apply infolist hooks.
     */
    protected function applyInfolistHooks(mixed $infolist): mixed
    {
        return FilamentHookBridge::applyInfolistHooks($infolist, $this->getHookContext());
    }

    /**
     * Apply action hooks.
     */
    protected function applyActionHooks(mixed $action): mixed
    {
        return FilamentHookBridge::applyActionHooks($action, $this->getHookContext());
    }

    /**
     * Get the context name for hooks.
     */
    protected function getHookContext(): string
    {
        return class_basename($this);
    }
}
