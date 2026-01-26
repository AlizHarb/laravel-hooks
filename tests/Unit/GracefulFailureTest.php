namespace Tests\Unit;

use AlizHarb\LaravelHooks\Facades\Hook;
use Illuminate\Support\Facades\Log;

test('graceful hooks do not throw when listeners fail', function () {
    Log::spy();

    Hook::addAction('failing.hook', function () {
        throw new Exception('Boom!');
    });

    // This should NOT throw
    Hook::gracefully()->doAction('failing.hook');

    Log::shouldHaveReceived('error')->withArgs(function ($message) {
        return str_contains($message, 'Hook [failing.hook] listener failed gracefully');
    });
});

test('non-graceful hooks still throw', function () {
    Hook::addAction('critical.hook', function () {
        throw new Exception('Boom!');
    });

    expect(fn() => Hook::doAction('critical.hook'))->toThrow(Exception::class);
});
