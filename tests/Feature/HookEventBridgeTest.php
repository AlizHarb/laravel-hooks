<?php

declare(strict_types=1);

namespace Tests\Feature;

use AlizHarb\LaravelHooks\Facades\Hook;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class HookEventBridgeTest extends TestCase
{
    public function test_event_bridge_triggers_hook(): void
    {
        $triggered = false;
        Hook::addAction('bridge.test', function () use (&$triggered) {
            $triggered = true;
        });

        Hook::bridge('some.laravel.event', 'bridge.test');

        Event::dispatch('some.laravel.event');

        $this->assertTrue($triggered);
    }

    public function test_event_bridge_passes_payload_correctly(): void
    {
        $received = null;
        Hook::addAction('bridge.payload', function ($data) use (&$received) {
            $received = $data;
        });

        Hook::bridge('payload.event', 'bridge.payload');

        Event::dispatch('payload.event', [['key' => 'value']]);

        $this->assertEquals(['key' => 'value'], $received);
    }
}
