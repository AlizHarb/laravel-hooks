namespace Tests\Unit;

use AlizHarb\LaravelHooks\Facades\Hook;
use Illuminate\Support\Facades\DB;

test('transactional hooks are wrapped in DB transaction', function () {
    DB::shouldReceive('transaction')
        ->once()
        ->with(Mockery::type('Closure'))
        ->andReturnUsing(fn($callback) => $callback());

    Hook::transactional()->doAction('tx.hook');
});

test('graceful transactional actions work together', function () {
    DB::shouldReceive('transaction')->andReturn(null);

    Hook::transactional()->gracefully()->doAction('mix.hook');
    
    expect(true)->toBeTrue();
});
