namespace Tests\Unit;

use AlizHarb\LaravelHooks\Facades\Hook;

enum UserHook: string {
    case Created = 'user.created';
    case Deleted = 'user.deleted';
}

test('backed enums can be used as hook names', function () {
    $executed = false;
    
    Hook::addAction(UserHook::Created, function() use (&$executed) {
        $executed = true;
    });
    
    Hook::doAction(UserHook::Created);
    
    expect($executed)->toBeTrue();
});

test('backed enums work with filters', function () {
    Hook::addFilter(UserHook::Created, fn($v) => $v . '_modified');
    
    $result = Hook::applyFilters(UserHook::Created, 'original');
    
    expect($result)->toBe('original_modified');
});
