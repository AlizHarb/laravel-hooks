<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Commands;

use AlizHarb\LaravelHooks\HookManager;
use Illuminate\Console\Command;

/**
 * Artisan command to inspect a specific hook and its listeners.
 */
class HookInspectCommand extends Command
{
    protected $signature = 'hook:inspect {hook : The name of the hook to inspect}';
    protected $description = 'Inspect callbacks and priority for a specific hook';

    /**
     * Execute the console command.
     *
     * @param HookManager $manager
     * @return int
     */
    public function handle(HookManager $manager): int
    {
        $hookName = $this->argument('hook');
        $filters = $manager->getFilters();

        if (! isset($filters[$hookName])) {
            $this->error("No callbacks registered for hook: {$hookName}");

            return self::FAILURE;
        }

        $this->info("Inspecting hook: {$hookName}");

        $rows = [];
        foreach ($filters[$hookName] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $callbackName = 'Closure';
                if (is_array($callback['function'])) {
                    $class = is_object($callback['function'][0]) ? get_class($callback['function'][0]) : $callback['function'][0];
                    $callbackName = $class . '@' . $callback['function'][1];
                } elseif (is_string($callback['function'])) {
                    $callbackName = $callback['function'];
                }

                $rows[] = [
                    $priority,
                    $callbackName,
                    $callback['accepted_args'],
                ];
            }
        }

        $this->table(
            ['Priority', 'Callback', 'Args'],
            $rows
        );

        return self::SUCCESS;
    }
}
