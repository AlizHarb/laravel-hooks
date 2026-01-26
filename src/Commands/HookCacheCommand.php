<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Commands;

use AlizHarb\LaravelHooks\HookCache;
use AlizHarb\LaravelHooks\HookDiscoverer;
use AlizHarb\LaravelHooks\HookManager;
use Illuminate\Console\Command;

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
     * @param HookDiscoverer $discoverer
     * @return int
     */
    public function handle(HookCache $cache, HookManager $manager, HookDiscoverer $discoverer): int
    {
        $this->info('Discovering hooks...');

        // Clear existing filters and run discovery
        $discoverer->discover();

        $this->info('Caching hooks...');

        $cache->put([
            'filters' => $manager->getFilters(),
            'wildcard_filters' => $manager->getWildcardFilters(),
        ]);

        $this->info('Hooks cached successfully!');

        return self::SUCCESS;
    }
}
