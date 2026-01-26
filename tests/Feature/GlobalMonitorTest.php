namespace Tests\Feature;

use AlizHarb\LaravelHooks\Facades\Hook;

test('global monitor catches any hook', function () {
    $executedHooks = [];

    Hook::addFilter('*', function (string $hook, array $args) use (&$executedHooks) {
        $executedHooks[] = $hook;
    });

    Hook::doAction('hook.one');
    Hook::applyFilters('hook.two', 'val');
    Hook::doAction('hook.three', 'arg1', 'arg2');

    expect($executedHooks)->toBe(['hook.one', 'hook.two', 'hook.three']);
});

test('global monitor can inspect arguments', function () {
    $capturedArgs = null;

    Hook::addFilter('*', function (string $hook, array $args) use (&$capturedArgs) {
        if ($hook === 'test.args') {
            $capturedArgs = $args;
        }
    });

    Hook::applyFilters('test.args', 'first', 'second');

    expect($capturedArgs)->toBe(['first', 'second']);
});
