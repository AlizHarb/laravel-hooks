<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks;

use AlizHarb\LaravelHooks\Facades\Hook;
use Illuminate\Support\Facades\Blade;

/**
 * Registers Blade directives for the Hook system.
 */
class BladeDirectives
{
    /**
     * Register the @hook and @filter directives.
     */
    public static function register(): void
    {
        Blade::directive('hook', function ($expression) {
            $parts = explode(',', $expression, 2);
            $hook = trim($parts[0]);
            $fallback = isset($parts[1]) ? trim($parts[1]) : null;

            if ($fallback) {
                return "<?php 
                    if (\AlizHarb\LaravelHooks\Facades\Hook::hasListeners($hook)) {
                        \AlizHarb\LaravelHooks\Facades\Hook::doAction($hook);
                    } else {
                        echo view($fallback)->render();
                    }
                ?>";
            }

            return "<?php \AlizHarb\LaravelHooks\Facades\Hook::doAction($expression); ?>";
        });

        Blade::directive('filter', function ($expression) {
            return "<?php echo \AlizHarb\LaravelHooks\Facades\Hook::applyFilters($expression); ?>";
        });

        Blade::if('hasHooks', function ($expression) {
            $hookNames = \AlizHarb\LaravelHooks\Facades\Hook::getRegisteredHookNames();

            return in_array(trim($expression, "'\""), $hookNames);
        });

        Blade::directive('hookIf', function ($expression) {
            $parts = explode(',', $expression, 2);
            $condition = trim($parts[0]);
            $args = trim($parts[1]);

            return "<?php if($condition) \AlizHarb\LaravelHooks\Facades\Hook::doAction($args); ?>";
        });
    }
}
