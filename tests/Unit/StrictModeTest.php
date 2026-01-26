namespace Tests\Unit;

use AlizHarb\LaravelHooks\Facades\Hook;
use AlizHarb\LaravelHooks\Exceptions\HookNotFoundException;
use Illuminate\Support\Facades\Config;

test('strict mode throws exception when no listeners exist', function () {
    Config::set('hooks.strict', true);

    expect(fn() => Hook::applyFilters('non.existent.hook', 'value'))
        ->toThrow(HookNotFoundException::class);
});

test('strict mode does not throw if listener exists', function () {
    Config::set('hooks.strict', true);

    Hook::addAction('existent.hook', fn() => null);

    Hook::doAction('existent.hook');
    
    expect(true)->toBeTrue();
});

test('no exception thrown when strict mode is disabled', function () {
    Config::set('hooks.strict', false);

    $result = Hook::applyFilters('non.existent.hook', 'default-value');
    
    expect($result)->toBe('default-value');
});
