<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Commands;

use AlizHarb\LaravelHooks\Facades\Hook;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class HookTraceCommand extends Command
{
    protected $signature = 'hook:trace {--hook= : Filter by specific hook name}';

    protected $description = 'Monitor hook execution and mutation in real-time';

    public function handle(): void
    {
        $this->info('Entering Hook Trace Mode... (Press Ctrl+C to stop)');
        $target = $this->option('hook');

        if ($target) {
            $this->line("Filtering for hook: <info>{$target}</info>");
        }

        // Enable internal tracing in the manager
        Hook::enableTracing();

        $this->line('Listening for events...');

        // We use a simple loop with a tiny sleep to check the "Execution Log"
        // This is a "Premium" observability feature
        /* @phpstan-ignore-next-line */
        while (true) {
            $logs = Hook::getRecentTraceLogs();

            foreach ($logs as $log) {
                if ($target && $log['hook'] !== $target) {
                    continue;
                }

                $this->renderLog($log);
            }

            usleep(100000); // 100ms
        }
    }

    protected function renderLog(array $log): void
    {
        $time = date('H:i:s');
        $this->line("[{$time}] <comment>{$log['hook']}</comment> executed in <info>{$log['time']}ms</info>");

        foreach ($log['mutations'] as $mut) {
            $this->line("  -> <info>{$mut['listener']}</info> changed value from <comment>\"{$mut['before']}\"</comment> to <comment>\"{$mut['after']}\"</comment>");
        }

        $this->line('');
    }
}
