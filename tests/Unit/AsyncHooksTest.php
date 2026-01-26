<?php

namespace Tests\Unit;

use AlizHarb\LaravelHooks\Facades\Hook;
use AlizHarb\LaravelHooks\Jobs\ProcessHookJob;
use Illuminate\Support\Facades\Queue;

test('queued actions dispatch process hook job', function () {
    Queue::fake();

    Hook::queueAction('email.send', 'test@example.com', 'Welcome!');

    Queue::assertPushed(ProcessHookJob::class, function ($job) {
        return $job->hook === 'email.send'
            && $job->args[0] === 'test@example.com'
            && $job->args[1] === 'Welcome!';
    });
});
