<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Debugbar;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use AlizHarb\LaravelHooks\HookInspector;

/**
 * DebugBar DataCollector for Hooks.
 */
class HookCollector extends DataCollector implements Renderable
{
    /**
     * Create a new HookCollector instance.
     *
     * @param HookInspector $inspector
     */
    public function __construct(
        protected HookInspector $inspector
    ) {}

    /**
     * Collect data for the DebugBar.
     *
     * @return array
     */
    public function collect(): array
    {
        $history = $this->inspector->getHistory();

        return [
            'count' => count($history),
            'hooks' => $history,
        ];
    }

    /**
     * Get the unique name of the collector.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'hooks';
    }

    /**
     * Get the widgets for the DebugBar.
     *
     * @return array
     */
    public function getWidgets(): array
    {
        return [
            'hooks' => [
                'icon' => 'puzzle-piece',
                'widget' => 'PhpDebugBar.Widgets.HtmlVariableListWidget',
                'map' => 'hooks.hooks',
                'default' => '[]',
            ],
            'hooks:badge' => [
                'map' => 'hooks.count',
                'default' => 0,
            ],
        ];
    }
}
