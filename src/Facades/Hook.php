<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Facades;

use AlizHarb\LaravelHooks\HookManager;
use AlizHarb\LaravelHooks\HookPipelineBuilder;
use BackedEnum;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \AlizHarb\LaravelHooks\PendingHookRegistration addAction(string|BackedEnum $hook, callable|string|array $callback, int $priority = 10, int $acceptedArgs = 1)
 * @method static \AlizHarb\LaravelHooks\PendingHookRegistration addFilter(string|BackedEnum $hook, callable|string|array $callback, int $priority = 10, int $acceptedArgs = 1)
 * @method static HookManager define(string|BackedEnum $hook, array $signature)
 * @method static HookManager deprecate(string|BackedEnum $old, string|BackedEnum $new, string $version)
 * @method static HookPipelineBuilder pipe(string|BackedEnum $hook)
 * @method static \AlizHarb\LaravelHooks\PendingHookCall doAction(string|BackedEnum $hook, mixed ...$args)
 * @method static void queueAction(string|BackedEnum $hook, mixed ...$args)
 * @method static mixed applyFilters(string|BackedEnum $hook, mixed $value, mixed ...$args)
 * @method static bool removeFilter(string|BackedEnum $hook, callable|string|array $callback, int $priority = 10)
 * @method static bool removeAction(string|BackedEnum $hook, callable|string|array $callback, int $priority = 10)
 * @method static void overrideView(string $original, string $override)
 * @method static string getOverriddenView(string $view)
 * @method static \AlizHarb\LaravelHooks\ScopedHookManager for(mixed $scope)
 * @method static HookManager gracefully()
 * @method static HookManager transactional()
 * @method static HookManager mute(string $hook)
 * @method static HookManager unmute(string $hook)
 * @method static HookManager silence()
 * @method static mixed withoutHooks(callable $callback)
 * @method static array getRegisteredHookNames()
 * @method static void setGraceful(string $hook)
 * @method static void onAny(callable $callback)
 *
 * @see \AlizHarb\LaravelHooks\HookManager
 */
class Hook extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return HookManager::class;
    }
}
