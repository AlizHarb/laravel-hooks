<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks;

use AlizHarb\LaravelHooks\Bridge\{FilamentHookBridge, LivewireHookBridge};
use AlizHarb\LaravelHooks\Commands\{HookCacheCommand, HookClearCommand, HookGenerateDocsCommand, HookIdeHelperCommand, HookInspectCommand, HookListCommand, HookMonitorCommand};
use AlizHarb\LaravelHooks\Debugbar\HookCollector;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;

class HookServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/hooks.php', 'hooks');

        $this->app->singleton(HookManager::class, function ($app) {
            return new HookManager(
                $app,
                $app->make(HookInspector::class)
            );
        });

        $this->app->singleton(HookDiscoverer::class, function ($app) {
            return new HookDiscoverer($app->make(HookManager::class));
        });

        $this->app->singleton(HookCache::class);

        $this->app->alias(HookManager::class, 'hooks');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        /** @var HookManager $manager */
        $manager = $this->app->make(HookManager::class);

        // Try loading from cache first
        if ($cached = $this->app->make(HookCache::class)->get()) {
            $manager->setFilters($cached['filters'] ?? []);
            $manager->setWildcardFilters($cached['wildcard_filters'] ?? []);
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/hooks.php' => config_path('hooks.php'),
            ], 'hooks-config');

            $this->commands([
                HookListCommand::class,
                HookInspectCommand::class,
                HookCacheCommand::class,
                HookClearCommand::class,
                HookIdeHelperCommand::class,
                HookMonitorCommand::class,
                HookGenerateDocsCommand::class,
            ]);
        }

        // Run discovery if not loaded from cache
        if (! $manager->isLoaded) {
            $this->app->make(HookDiscoverer::class)->discover();
        }

        if ($this->app->bound('debugbar')) {
            $this->app['debugbar']->addCollector(
                new HookCollector(
                    $this->app->make(HookInspector::class)
                )
            );
        }

        BladeDirectives::register();

        if (config('hooks.filament_bridge.enabled', false)) {
            $this->app->make(FilamentHookBridge::class)->register();
        }

        if (config('hooks.livewire_bridge.enabled', false)) {
            $this->app->make(LivewireHookBridge::class)->register();
        }

        AboutCommand::add('Laravel Hooks', fn () => ['Version' => '1.1.0', 'Author' => 'Ali Harb']);
    }
}
