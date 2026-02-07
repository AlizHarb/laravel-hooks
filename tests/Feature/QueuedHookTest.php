<?php

declare(strict_types=1);

namespace Tests\Feature;

use AlizHarb\LaravelHooks\Facades\Hook;
use AlizHarb\LaravelHooks\Jobs\HookJob;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QueuedHookTest extends TestCase
{
    public function test_it_can_queue_an_action_listener(): void
    {
        Queue::fake();

        // Using a static method or class@method since closures can't be queued
        Hook::addAction('test.queued.hook', 'Tests\Feature\QueuedHookHandler@handle')
            ->onQueue();

        Hook::doAction('test.queued.hook', 'data');

        Queue::assertPushed(HookJob::class, function ($job) {
            return true;
        });
    }

    public function test_it_throws_exception_if_closure_is_queued(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Closures cannot be queued');

        Hook::addAction('test.fail', function () {})->onQueue();
    }
}

class QueuedHookHandler
{
    public static function handle($data)
    {
        // ...
    }
}
