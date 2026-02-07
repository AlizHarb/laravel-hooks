<?php

declare(strict_types=1);

namespace Tests\Feature;

use AlizHarb\LaravelHooks\Facades\Hook;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Mockery;
use Tests\TestCase;

class RoadmapFeaturesTest extends TestCase
{
    public function test_circuit_breaker_trips_after_threshold(): void
    {
        Log::shouldReceive('error')->atLeast()->times(3);
        Log::shouldReceive('critical')->once()->with(Mockery::pattern('/tripped its circuit breaker/'));

        Config::set('hooks.circuit_breaker.threshold', 3);

        $count = 0;
        Hook::addAction('failing.hook', function () use (&$count) {
            $count++;

            throw new \Exception('Boom');
        });
        Hook::setGraceful('failing.hook');

        // Execution 1
        Hook::doAction('failing.hook');
        $this->assertEquals(1, $count);

        // Execution 2
        Hook::doAction('failing.hook');
        $this->assertEquals(2, $count);

        // Execution 3 - Threshold reached
        Hook::doAction('failing.hook');
        $this->assertEquals(3, $count);

        // Execution 4 - Should be ignored because of circuit breaker
        Hook::doAction('failing.hook');
        $this->assertEquals(3, $count, 'Hook should have been silenced by circuit breaker');
    }

    public function test_blade_hook_renders_fallback_when_no_listeners(): void
    {
        View::addNamespace('test', __DIR__.'/../resources/views');

        // Mock a view for the fallback
        $mockView = Mockery::mock(\Illuminate\Contracts\View\View::class);
        $mockView->shouldReceive('render')->andReturn('Fallback Content');

        View::shouldReceive('exists')->andReturn(true);
        View::shouldReceive('make')->with('test::fallback', Mockery::any(), Mockery::any())
            ->andReturn($mockView);

        $blade = "@hook('unknown.hook', 'test::fallback')";
        $php = \Illuminate\Support\Facades\Blade::compileString($blade);

        // Evaluate the output
        ob_start();
        eval('?>'.$php);
        $output = ob_get_clean();

        $this->assertEquals('Fallback Content', trim($output));
    }

    public function test_blade_hook_does_not_render_fallback_when_listeners_exist(): void
    {
        Hook::addAction('known.hook', function () {
            echo 'Action Content';
        });

        $blade = "@hook('known.hook', 'test::fallback')";
        $php = \Illuminate\Support\Facades\Blade::compileString($blade);

        ob_start();
        eval('?>'.$php);
        $output = ob_get_clean();

        $this->assertEquals('Action Content', trim($output));
    }
}
