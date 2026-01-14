<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks;

use AlizHarb\LaravelHooks\Bridge\EloquentHookBridge;
use AlizHarb\LaravelHooks\Commands\{HookCacheCommand, HookClearCommand, HookIdeHelperCommand, HookInspectCommand, HookListCommand};
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

        $this->app->alias(HookManager::class, 'hooks');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
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
            ]);
        }

        if ($this->app->bound('debugbar')) {
            $this->app['debugbar']->addCollector(
                new HookCollector(
                    $this->app->make(HookInspector::class)
                )
            );
        }

        BladeDirectives::register();

        // View Creator for Overrides (ViewFinder approach recommended for deeper integration)
        $this->app['view']->creator('*', function ($view) {
             // Hook system allows overriding views at runtime
        });

        if (config('hooks.eloquent_bridge', true)) {
            $this->app->make(EloquentHookBridge::class)->register();
        }

        AboutCommand::add('Laravel Hooks', fn () => ['Version' => '1.0.0', 'Author' => 'Ali Harb']);
    }
}
