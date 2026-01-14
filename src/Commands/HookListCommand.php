<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Commands;

use Illuminate\Console\Command;
use AlizHarb\LaravelHooks\HookManager;

/**
 * Artisan command to list registered hooks in the system.
 */
class HookListCommand extends Command
{
    protected $signature = 'hook:list';
    protected $description = 'List all registered hooks and their listeners';

    /**
     * Execute the console command.
     *
     * @param HookManager $manager
     * @return int
     */
    public function handle(HookManager $manager): int
    {
        $hooks = $manager->getFilters();

        $rows = [];
        foreach ($hooks as $name => $priorities) {
            foreach ($priorities as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    $callbackName = 'Closure';
                    if (is_array($callback['function'])) {
                         $class = is_object($callback['function'][0]) ? get_class($callback['function'][0]) : $callback['function'][0];
                         $callbackName = $class . '@' . $callback['function'][1];
                    } elseif (is_string($callback['function'])) {
                        $callbackName = $callback['function'];
                    }

                    $rows[] = [
                        $name,
                        $priority,
                        $callbackName,
                        $callback['accepted_args']
                    ];
                }
            }
        }

        $this->table(
            ['Hook', 'Priority', 'Callback', 'Args'],
            $rows
        );

        return self::SUCCESS;
    }
}
