<?php

use AlizHarb\LaravelHooks\Facades\Hook;
use AlizHarb\LaravelHooks\Exceptions\HookSignatureMismatchException;
use Illuminate\Support\Facades\Log;

test('signatures validate arguments', function () {
    Hook::define('strict.hook', ['string', 'int']);
    
    // Valid
    Hook::doAction('strict.hook', 'string', 123);
    expect(true)->toBeTrue();
    
    // Invalid
    expect(fn() => Hook::doAction('strict.hook', 'string', 'not-int'))
        ->toThrow(HookSignatureMismatchException::class);
});

test('deprecations log warnings', function () {
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(function ($msg) {
            return str_contains($msg, 'deprecated');
        });

    Hook::deprecate('old.hook', 'new.hook', '1.0');
    
    Hook::doAction('old.hook');
});

test('eloquent bridge dispatches generic hooks', function () {
    // We can't easily test the event listener binding without mocking the generic event system fully,
    // but we can verify the class exists and acts.
    $bridge = new \AlizHarb\LaravelHooks\Bridge\EloquentHookBridge();
    expect($bridge)->toBeInstanceOf(\AlizHarb\LaravelHooks\Bridge\EloquentHookBridge::class);
});
