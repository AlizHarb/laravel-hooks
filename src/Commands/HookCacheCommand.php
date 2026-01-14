<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Commands;

use Illuminate\Console\Command;
use AlizHarb\LaravelHooks\HookCache;
use AlizHarb\LaravelHooks\HookManager;

/**
 * Caches hook definitions for better performance.
 */
class HookCacheCommand extends Command
{
    protected $signature = 'hook:cache';
    protected $description = 'Cache the discovered hooks for performance';

    /**
     * Execute the console command.
     *
     * @param HookCache $cache
     * @param HookManager $manager
     * @return int
     */
    public function handle(HookCache $cache, HookManager $manager): int
    {
        $this->info('Caching hooks...');

        $hooks = [
            'actions' => [],
            'filters' => [],
        ];

        $content = '<?php return ' . var_export($hooks, true) . ';';

        if (! is_dir(base_path('bootstrap/cache'))) {
            mkdir(base_path('bootstrap/cache'), 0755, true);
        }

        file_put_contents(
            base_path('bootstrap/cache/hooks.php'),
            $content
        );

        $this->info('Hooks cached successfully!');

        return self::SUCCESS;
    }
}
