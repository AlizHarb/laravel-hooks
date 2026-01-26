namespace Tests\Unit;

use AlizHarb\LaravelHooks\Facades\Hook;

test('conditional hooks activation with when()', function () {
    $active = false;

    Hook::addFilter('conditional.hook', fn($v) => 'new value')
        ->when(fn() => $active);

    expect(Hook::applyFilters('conditional.hook', 'old value'))->toBe('old value');

    $active = true;
    
    // We have to register it again because the first time it was removed
    Hook::addFilter('conditional.hook', fn($v) => 'new value')
        ->when(fn() => $active);

    expect(Hook::applyFilters('conditional.hook', 'old value'))->toBe('new value');
});

test('conditional hooks activation with onlyInEnvironment()', function () {
    $currentEnv = app()->environment();

    Hook::addFilter('env.hook', fn($v) => 'production value')
        ->onlyInEnvironment('production');

    if ($currentEnv === 'production') {
        expect(Hook::applyFilters('env.hook', 'default'))->toBe('production value');
    } else {
        expect(Hook::applyFilters('env.hook', 'default'))->toBe('default');
    }
});
