# Basics

## HookManager

The `HookManager` is the brain of the package. It manages all actions and filters.
You typically interact with it via the `Hook` facade.

## Actions vs Filters

### Actions (`addAction`, `doAction`)

Actions are events. They allow you to "do something" at a specific point in execution. They do not return a value.

```php
Hook::addAction('order.created', function ($order) {
    // Send email
});

Hook::doAction('order.created', $order);
```

### Filters (`addFilter`, `applyFilters`)

Filters allow you to modify data. They always return a value.

```php
Hook::addFilter('cart.total', function ($total) {
    return $total * 1.1; // Add tax
});

$total = Hook::applyFilters('cart.total', 100);
```

## Priorities

Hooks run in order of priority (default 10). Lower numbers run earlier.

```php
Hook::addFilter('title', fn($t) => $t . ' (First)', 5);
Hook::addFilter('title', fn($t) => $t . ' (Last)', 20);
```

## Attributes (PHP 8+)

You can register hooks directly on methods using attributes. This is the recommended way to register hooks as it keeps your logic organized and allows for automatic discovery.

```php
use AlizHarb\LaravelHooks\Attributes\HookAction;
use AlizHarb\LaravelHooks\Attributes\HookFilter;

class UserObserver 
{
    #[HookAction(hook: 'user.created', priority: 20)]
    public function onCreated($user) 
    {
        // Logic here
    }

    #[HookFilter(hook: 'user.display_name')]
    public function formatName($name) 
    {
        return strtoupper($name);
    }
}
```

### Auto Discovery

The package will automatically scan the paths defined in `config/hooks.php` (specifically the `scan_paths` array) for any classes containing these attributes. By default, it looks in `app/Hooks` and `app/Listeners`.

You can also manually trigger discovery and cache the results for production using:

```bash
php artisan hook:cache
```
