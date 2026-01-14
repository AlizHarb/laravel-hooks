<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks;

use Illuminate\Support\Facades\Blade;
use AlizHarb\LaravelHooks\Facades\Hook;

/**
 * Registers Blade directives for the Hook system.
 */
class BladeDirectives
{
    /**
     * Register the @hook and @filter directives.
     *
     * @return void
     */
    public static function register(): void
    {
        Blade::directive('hook', function ($expression) {
            return "<?php \AlizHarb\LaravelHooks\Facades\Hook::doAction($expression); ?>";
        });

        Blade::directive('filter', function ($expression) {
            return "<?php echo \AlizHarb\LaravelHooks\Facades\Hook::applyFilters($expression); ?>";
        });
    }
}
