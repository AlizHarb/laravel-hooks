namespace Tests\Unit;

use AlizHarb\LaravelHooks\Facades\Hook;

test('individual hooks can be muted', function () {
    $fired = false;
    Hook::addAction('muted.hook', function() use (&$fired) { $fired = true; });

    Hook::mute('muted.hook');
    Hook::doAction('muted.hook');
    expect($fired)->toBeFalse();

    Hook::unmute('muted.hook');
    Hook::doAction('muted.hook');
    expect($fired)->toBeTrue();
});

test('all hooks can be silenced', function () {
    $count = 0;
    Hook::addAction('a', function() use (&$count) { $count++; });
    Hook::addAction('b', function() use (&$count) { $count++; });

    Hook::silence();
    Hook::doAction('a');
    Hook::doAction('b');
    expect($count)->toBe(0);
});

test('withoutHooks helper suppresses execution', function () {
    $fired = false;
    Hook::addAction('test', function() use (&$fired) { $fired = true; });

    Hook::withoutHooks(function() {
        Hook::doAction('test');
    });

    expect($fired)->toBeFalse();
    
    Hook::doAction('test');
    expect($fired)->toBeTrue();
});
