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

## Attributes (PHP 8.5)

You can register hooks directly on methods using attributes.

```php
class UserObserver {
    #[HookAction('user.created')]
    public function onCreated($user) { ... }
}
```
