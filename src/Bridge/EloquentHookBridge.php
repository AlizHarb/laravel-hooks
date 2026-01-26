<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Bridge;

use AlizHarb\LaravelHooks\Facades\Hook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

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
        $config = config('hooks.eloquent_bridge', []);

        if (! ($config['enabled'] ?? false)) {
            return;
        }

        $events = $config['events'] ?? ['eloquent.*'];

        foreach ($events as $eventPattern) {
            Event::listen($eventPattern, function (string $event, array $payload) use ($config) {
                if (empty($payload)) {
                    return;
                }

                $model = $payload[0];

                if (! $model instanceof Model) {
                    return;
                }

                $modelClass = get_class($model);

                // Filter by event (excludes)
                $exceptEvents = $config['except_events'] ?? [];
                foreach ($exceptEvents as $pattern) {
                    if (Str::is($pattern, $event)) {
                        return;
                    }
                }

                // Filter by model (includes)
                $allowedModels = $config['models'] ?? [];
                if (! empty($allowedModels)) {
                    $matched = false;
                    foreach ($allowedModels as $pattern) {
                        if (Str::is($pattern, $modelClass)) {
                            $matched = true;

                            break;
                        }
                    }
                    if (! $matched) {
                        return;
                    }
                }

                // Filter by model (excludes)
                $exceptModels = $config['except_models'] ?? [];
                foreach ($exceptModels as $pattern) {
                    if (Str::is($pattern, $modelClass)) {
                        return;
                    }
                }

                // Dispatch exact event name as a hook action
                Hook::doAction($event, $model);

                // Also dispatch model-specific hook for easier targeting
                $shortName = strtolower(class_basename($model));
                $actionName = str_replace('eloquent.', "model.{$shortName}.", $event);
                Hook::doAction($actionName, $model);
            });
        }
    }
}
