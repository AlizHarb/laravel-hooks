<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Bridge;

use AlizHarb\LaravelHooks\Facades\Hook;
use Livewire\Livewire;

class LivewireHookBridge
{
    /**
     * Register global Livewire listeners.
     *
     * @return void
     */
    public function register(): void
    {
        if (! class_exists(Livewire::class)) {
            return;
        }

        $config = config('hooks.livewire_bridge', []);

        if (! ($config['enabled'] ?? false)) {
            return;
        }

        // Bridge common Livewire events to Hooks
        $this->bridge('component.boot', 'boot');
        $this->bridge('component.mount', 'mount');
        $this->bridge('component.render', 'render');
        $this->bridge('component.dehydrate', 'dehydrate');
    }

    /**
     * Bridge a Livewire event to a Hook action.
     *
     * @param string $livewireEvent
     * @param string $hookSuffix
     * @return void
     */
    protected function bridge(string $livewireEvent, string $hookSuffix): void
    {
        Livewire::listen($livewireEvent, function ($component, $params = []) use ($hookSuffix) {
            $name = class_basename($component);

            // Fire component-specific hook
            Hook::doAction("livewire.{$name}.{$hookSuffix}", $component, $params);

            // Fire general hook
            Hook::doAction("livewire.{$hookSuffix}", $component, $params);
        });
    }
}
