<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Traits;

use AlizHarb\LaravelHooks\Facades\Hook;

trait InteractsWithLivewireHooks
{
    /**
     * Boot the hooks trait.
     */
    public function bootInteractsWithLivewireHooks(): void
    {
        $this->fireLivewireHook('boot');
    }

    /**
     * Hook into the mount lifecycle.
     *
     * @param  mixed  ...$args
     */
    public function mountInteractsWithLivewireHooks(...$args): void
    {
        $this->fireLivewireHook('mount', ...$args);
    }

    /**
     * Hook into the rendering lifecycle.
     */
    public function renderingInteractsWithLivewireHooks(): void
    {
        $this->fireLivewireHook('rendering');
    }

    /**
     * Hook into the rendered lifecycle.
     *
     * @param  mixed  $view
     */
    public function renderedInteractsWithLivewireHooks($view): void
    {
        $this->fireLivewireHook('rendered', $view);
    }

    /**
     * Hook into the updating lifecycle.
     *
     * @param  mixed  $value
     */
    public function updatingInteractsWithLivewireHooks(string $name, $value): void
    {
        $this->fireLivewireHook("updating.{$name}", $value);
    }

    /**
     * Hook into the updated lifecycle.
     *
     * @param  mixed  $value
     */
    public function updatedInteractsWithLivewireHooks(string $name, $value): void
    {
        $this->fireLivewireHook("updated.{$name}", $value);
    }

    /**
     * Fire a Livewire-specific hook.
     *
     * @param  mixed  ...$args
     */
    protected function fireLivewireHook(string $event, ...$args): void
    {
        $componentName = class_basename($this);

        Hook::doAction("livewire.{$componentName}.{$event}", $this, ...$args);
        Hook::doAction("livewire.{$event}", $this, ...$args);
    }
}
