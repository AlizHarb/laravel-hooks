<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Bridge;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use AlizHarb\LaravelHooks\Facades\Hook;

/**
 * Bridging Eloquent events to the Hook system.
 */
class EloquentHookBridge
{
    /**
     * Register listeners for Eloquent events.
     *
     * @return void
     */
    public function register(): void
    {
        Event::listen('eloquent.*', function (string $event, array $payload) {
            if (empty($payload)) {
                return;
            }

            $model = $payload[0];

            if (! $model instanceof Model) {
                return;
            }

            // Dispatch exact event name as a hook action
            Hook::doAction($event, $model);
        });
    }
}
