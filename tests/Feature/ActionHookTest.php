<?php

declare(strict_types=1);

namespace Tests\Feature;

use AlizHarb\LaravelHooks\Facades\Hook;
use AlizHarb\LaravelHooks\Traits\MakesActionsHookable;
use Tests\TestCase;

class SampleAction
{
    use MakesActionsHookable;

    public function run(string $input): string
    {
        return $this->executeWithHooks(fn ($data) => strtoupper($data), $input);
    }
}

class ActionHookTest extends TestCase
{
    public function test_action_triggers_executing_and_executed_hooks(): void
    {
        $log = [];

        Hook::addAction('action.tests.feature.sampleaction.executing', function () use (&$log) {
            $log[] = 'executing';
        });

        Hook::addAction('action.tests.feature.sampleaction.executed', function () use (&$log) {
            $log[] = 'executed';
        });

        $action = new SampleAction();
        $result = $action->run('hello');

        $this->assertEquals('HELLO', $result);
        $this->assertEquals(['executing', 'executed'], $log);
    }

    public function test_action_result_can_be_filtered(): void
    {
        Hook::addFilter('action.tests.feature.sampleaction.result', fn ($res) => $res.'!!!');

        $action = new SampleAction();
        $result = $action->run('hello');

        $this->assertEquals('HELLO!!!', $result);
    }

    public function test_action_supports_pipeline_wrapping(): void
    {
        // A pipeline hook that wraps the execution
        Hook::addFilter('action.tests.feature.sampleaction.pipeline', function ($next, $action, $data) {
            $response = $next($data);

            return "Wrapped({$response})";
        }, 10, 3);

        $action = new SampleAction();
        $result = $action->run('hook');

        $this->assertEquals('Wrapped(HOOK)', $result);
    }
}
