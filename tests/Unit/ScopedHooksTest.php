namespace Tests\Unit;

use AlizHarb\LaravelHooks\Facades\Hook;

test('scoped hooks are isolated', function () {
    $scope1 = new stdClass();
    $scope2 = new stdClass();
    
    Hook::for($scope1)->addFilter('name', fn() => 'scope1');
    Hook::for($scope2)->addFilter('name', fn() => 'scope2');
    
    expect(Hook::for($scope1)->applyFilters('name', 'default'))->toBe('scope1');
    expect(Hook::for($scope2)->applyFilters('name', 'default'))->toBe('scope2');
    
    // Global hook should not be affected
    expect(Hook::applyFilters('name', 'default'))->toBe('default');
});

test('scoped actions are fired correctly', function () {
    $scope = new stdClass();
    $fired = false;
    
    Hook::for($scope)->addAction('ping', function() use (&$fired) {
        $fired = true;
    });
    
    Hook::doAction('ping');
    expect($fired)->toBeFalse();
    
    Hook::for($scope)->doAction('ping');
    expect($fired)->toBeTrue();
});
