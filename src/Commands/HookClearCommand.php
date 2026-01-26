<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Commands;

use AlizHarb\LaravelHooks\HookCache;
use Illuminate\Console\Command;

/**
 * Clears the hook cache.
 */
class HookClearCommand extends Command
{
    protected $signature = 'hook:clear';
    protected $description = 'Clear the hook cache';

    /**
     * Execute the console command.
     *
     * @param HookCache $cache
     * @return int
     */
    public function handle(HookCache $cache): int
    {
        $cache->forget();
        $this->info('Hook cache cleared.');

        return self::SUCCESS;
    }
}
