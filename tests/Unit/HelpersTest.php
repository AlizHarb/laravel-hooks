namespace Tests\Unit;

use AlizHarb\LaravelHooks\Facades\Hook;

test('do_action helper works', function () {
    $executed = false;
    Hook::addAction('test.action', function() use (&$executed) {
        $executed = true;
    });

    do_action('test.action');

    expect($executed)->toBeTrue();
});

test('apply_filters helper works', function () {
    Hook::addFilter('test.filter', fn($v) => $v . '_filtered');

    $result = apply_filters('test.filter', 'original');

    expect($result)->toBe('original_filtered');
});

test('hook helper works', function () {
    // Global
    expect(hook())->toBeInstanceOf(\AlizHarb\LaravelHooks\HookManager::class);

    // Scoped
    $scope = new stdClass();
    expect(\hook($scope))->toBeInstanceOf(\AlizHarb\LaravelHooks\ScopedHookManager::class);
});
