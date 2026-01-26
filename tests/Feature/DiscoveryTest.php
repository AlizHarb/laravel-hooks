namespace Tests\Feature;

use AlizHarb\LaravelHooks\HookDiscoverer;
use AlizHarb\LaravelHooks\HookManager;

class TestHookListener
{
    #[HookAction(hook: 'test.attribute.action', priority: 15)]
    public function onAction(): void
    {
        //
    }

    #[HookFilter(hook: 'test.attribute.filter', priority: 5)]
    public function onFilter($value): string
    {
        return $value . ' edited';
    }
}

class TestHookDiscoverer extends HookDiscoverer
{
    public function discoverClass(string $className): void
    {
        $this->discoverInClass($className);
    }
}

test('attributes are discovered', function () {
    $manager = app(HookManager::class);
    $discoverer = new TestHookDiscoverer($manager);

    $discoverer->discoverClass(TestHookListener::class);

    $filters = $manager->getFilters();

    expect($filters)->toHaveKey('test.attribute.action')
        ->and($filters)->toHaveKey('test.attribute.filter');
    
    expect($filters['test.attribute.action'][15])->toBeArray();
    expect($filters['test.attribute.filter'][5])->toBeArray();

    // Verify filter execution
    $result = Hook::applyFilters('test.attribute.filter', 'original');
    expect($result)->toBe('original edited');
});
