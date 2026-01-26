<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Debugbar;

use AlizHarb\LaravelHooks\HookInspector;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

/**
 * DebugBar DataCollector for Hooks.
 */
class HookCollector extends DataCollector implements Renderable
{
    /**
     * Create a new HookCollector instance.
     */
    public function __construct(
        protected HookInspector $inspector
    ) {}

    /**
     * Collect data for the DebugBar.
     */
    public function collect(): array
    {
        $history = $this->inspector->getHistory();
        $hooks = [];

        foreach ($history as $i => $entry) {
            $key = sprintf('%02d. %s', $i + 1, $entry['hook']);
            $hooks[$key] = [
                'value' => $entry['value'],
                'args' => $entry['args'],
                'time' => date('H:i:s', (int) $entry['microtime']).'.'.sprintf('%03d', (int) (($entry['microtime'] - (int) $entry['microtime']) * 1000)),
                'memory' => round($entry['memory'] / 1024 / 1024, 2).' MB',
            ];
        }

        return [
            'count' => count($history),
            'hooks' => $hooks,
        ];
    }

    /**
     * Get the unique name of the collector.
     */
    public function getName(): string
    {
        return 'hooks';
    }

    /**
     * Get the widgets for the DebugBar.
     */
    public function getWidgets(): array
    {
        return [
            'hooks' => [
                'icon' => 'puzzle-piece',
                'widget' => 'PhpDebugBar.Widgets.HtmlVariableListWidget',
                'map' => 'hooks.hooks',
                'default' => '{}',
            ],
            'hooks:badge' => [
                'map' => 'hooks.count',
                'default' => 0,
            ],
        ];
    }
}
