<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Commands;

use AlizHarb\LaravelHooks\HookManager;
use Illuminate\Console\Command;

class HookMonitorCommand extends Command
{
    protected $signature = 'hook:monitor {--filter= : Filter by hook name}';
    protected $description = 'Monitor hook executions in real-time';

    public function handle(HookManager $manager): int
    {
        $this->info('Monitoring hooks... Press Ctrl+C to stop.');
        $this->line('');

        $filter = $this->option('filter');

        $manager->addFilter('*', function (string $hook, array $args) use ($filter) {
            if ($filter && ! str_contains($hook, $filter)) {
                return;
            }

            $time = date('H:i:s');
            $this->line("<fg=gray>[{$time}]</> <fg=cyan>{$hook}</>");

            foreach ($args as $index => $arg) {
                $type = is_object($arg) ? get_class($arg) : gettype($arg);
                $this->line("  <fg=gray>#{$index} ({$type})</>");
            }
        });

        // Loop indefinitely to keep the process alive
        // @phpstan-ignore-next-line
        while (true) {
            usleep(100000);
        }

    }
}
