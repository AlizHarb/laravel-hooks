namespace Tests\Unit;

use AlizHarb\LaravelHooks\Facades\Hook;
use function PHPUnit\Framework\assertEquals;

test('hooks can add actions and filters', function () {
    Hook::addAction('test.action', function () {
        // action logic
    });
    
    Hook::addFilter('test.filter', function ($value) {
        return $value . ' filtered';
    });
    
    $filters = Hook::getFilters();
    
    expect($filters)->toHaveKey('test.action')
        ->and($filters)->toHaveKey('test.filter');
});

test('filters modify values', function () {
    Hook::addFilter('modify_value', function ($value) {
        return $value * 2;
    });
    
    $result = Hook::applyFilters('modify_value', 10);
    
    expect($result)->toBe(20);
});

test('actions run logic', function () {
    $container = new stdClass();
    $container->value = 0;
    
    Hook::addAction('increment_value', function () use ($container) {
        $container->value++;
    });
    
    Hook::doAction('increment_value');
    
    expect($container->value)->toBe(1);
});

test('priorities rule execution order', function () {
    Hook::addFilter('order', function ($str) { return $str . 'A'; }, 20);
    Hook::addFilter('order', function ($str) { return $str . 'B'; }, 10);
    
    // Priority 10 runs first (B), then 20 (A)
    // "start" -> "startB" -> "startBA"
    $result = Hook::applyFilters('order', 'start');
    
    expect($result)->toBe('startBA');
});
