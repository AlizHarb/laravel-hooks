<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Commands;

use AlizHarb\LaravelHooks\HookManager;
use Illuminate\Console\Command;

/**
 * Artisan command to list registered hooks in the system.
 */
class HookListCommand extends Command
{
    protected $signature = 'hook:list {--search= : Filter by hook name}';

    protected $description = 'List all registered hooks and their listeners';

    /**
     * Execute the console command.
     */
    public function handle(HookManager $manager): int
    {
        $hooks = $manager->getFilters();
        $search = $this->option('search');

        if (empty($hooks)) {
            $this->warn('No hooks registered.');

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($hooks as $name => $priorities) {
            if ($search && ! str_contains($name, $search)) {
                continue;
            }

            foreach ($priorities as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    $callbackName = 'Closure';
                    if (is_array($callback['function'])) {
                        $class = is_object($callback['function'][0]) ? get_class($callback['function'][0]) : $callback['function'][0];
                        $callbackName = $class.'@'.$callback['function'][1];
                    } elseif (is_string($callback['function'])) {
                        $callbackName = $callback['function'];
                    }

                    $rows[] = [
                        $name,
                        $priority,
                        $callbackName,
                        $callback['accepted_args'],
                    ];
                }
            }
        }

        if (empty($rows)) {
            $this->warn("No hooks found matching [{$search}].");

            return self::SUCCESS;
        }

        $this->table(
            ['Hook', 'Priority', 'Callback', 'Args'],
            $rows
        );

        return self::SUCCESS;
    }
}
