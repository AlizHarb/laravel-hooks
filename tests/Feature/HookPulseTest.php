namespace Tests\Feature;

use AlizHarb\LaravelHooks\Events\HookExecuted;
use AlizHarb\LaravelHooks\Facades\Hook;
use Illuminate\Support\Facades\Event;

test('executing hook fires HookExecuted event', function () {
    Event::fake();

    Hook::doAction('monitored.hook', 'arg');

    Event::assertDispatched(HookExecuted::class, function ($event) {
        return $event->hook === 'monitored.hook';
    });
});
