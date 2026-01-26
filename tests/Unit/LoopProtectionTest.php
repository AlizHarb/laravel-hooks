namespace Tests\Unit;

use AlizHarb\LaravelHooks\Facades\Hook;
use Illuminate\Support\Facades\Config;

test('infinite hook loops are prevented', function () {
    Config::set('hooks.max_nesting', 5);

    Hook::addAction('loop', function () {
        Hook::doAction('loop');
    });

    expect(fn() => Hook::doAction('loop'))->toThrow(RuntimeException::class, 'Hook loop detected');
});

test('nesting within limit is allowed', function () {
    Config::set('hooks.max_nesting', 10);
    $count = 0;

    Hook::addAction('nested', function () use (&$count) {
        $count++;
        if ($count < 5) {
            Hook::doAction('nested');
        }
    });

    Hook::doAction('nested');
    expect($count)->toBe(5);
});
