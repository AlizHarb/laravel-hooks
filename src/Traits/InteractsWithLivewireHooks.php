<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Traits;

use AlizHarb\LaravelHooks\Facades\Hook;

trait InteractsWithLivewireHooks
{
    /**
     * Boot the hooks trait.
     *
     * @return void
     */
    public function bootInteractsWithLivewireHooks(): void
    {
        $this->fireLivewireHook('boot');
    }

    /**
     * Hook into the mount lifecycle.
     *
     * @param mixed ...$args
     * @return void
     */
    public function mountInteractsWithLivewireHooks(...$args): void
    {
        $this->fireLivewireHook('mount', ...$args);
    }

    /**
     * Hook into the rendering lifecycle.
     *
     * @return void
     */
    public function renderingInteractsWithLivewireHooks(): void
    {
        $this->fireLivewireHook('rendering');
    }

    /**
     * Hook into the rendered lifecycle.
     *
     * @param mixed $view
     * @return void
     */
    public function renderedInteractsWithLivewireHooks($view): void
    {
        $this->fireLivewireHook('rendered', $view);
    }

    /**
     * Hook into the updating lifecycle.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function updatingInteractsWithLivewireHooks(string $name, $value): void
    {
        $this->fireLivewireHook("updating.{$name}", $value);
    }

    /**
     * Hook into the updated lifecycle.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function updatedInteractsWithLivewireHooks(string $name, $value): void
    {
        $this->fireLivewireHook("updated.{$name}", $value);
    }

    /**
     * Fire a Livewire-specific hook.
     *
     * @param string $event
     * @param mixed ...$args
     * @return void
     */
    protected function fireLivewireHook(string $event, ...$args): void
    {
        $componentName = class_basename($this);

        Hook::doAction("livewire.{$componentName}.{$event}", $this, ...$args);
        Hook::doAction("livewire.{$event}", $this, ...$args);
    }
}
