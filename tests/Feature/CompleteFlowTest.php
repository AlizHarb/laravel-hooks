namespace Tests\Feature;

use AlizHarb\LaravelHooks\Attributes\{HookAction, HookFilter};
use AlizHarb\LaravelHooks\Facades\Hook;
use AlizHarb\LaravelHooks\HookDiscoverer;
use AlizHarb\LaravelHooks\HookManager;

class IntegratedListener
{
    #[HookFilter(hook: 'flow.step.1')]
    public function step1($val): string
    {
        return $val . ' -> Step 1';
    }

    #[HookAction(hook: 'flow.step.2')]
    public function step2(): void
    {
        // Action logic
    }
}

class FlowHookDiscoverer extends HookDiscoverer
{
    public function discoverClass(string $className): void
    {
        $this->discoverInClass($className);
    }
}

test('complete hook system flow', function () {
    $manager = app(HookManager::class);
    $discoverer = new FlowHookDiscoverer($manager);

    // 1. Discovery
    $discoverer->discoverClass(IntegratedListener::class);

    // 2. Global Monitoring
    $log = [];
    Hook::onAny(function (string $hook) use (&$log) {
        $log[] = "Fired: {$hook}";
    });

    // 3. Conditional filter
    $isVip = false;
    Hook::addFilter('flow.step.1', fn($v) => $v . ' [VIP]', 5)
        ->when(fn() => $isVip);

    // 4. Graceful execution
    Hook::addAction('flow.step.2', function () {
        throw new Exception('Graceful error');
    });

    // EXECUTION FLOW
    
    // Step 1: Filter (should not have [VIP])
    $result = Hook::applyFilters('flow.step.1', 'Start');
    expect($result)->toBe('Start -> Step 1');

    // Step 2: Action (should fail gracefully)
    Hook::gracefully()->doAction('flow.step.2');

    // Verify logs
    expect($log)->toContain('Fired: flow.step.1');
    expect($log)->toContain('Fired: flow.step.2');

    // Enable VIP and check again
    $isVip = true;
    Hook::addFilter('flow.step.1', fn($v) => $v . ' [VIP]', 5)
        ->when(fn() => $isVip);

    $resultVip = Hook::applyFilters('flow.step.1', 'Start');
    expect($resultVip)->toBe('Start [VIP] -> Step 1');
});
