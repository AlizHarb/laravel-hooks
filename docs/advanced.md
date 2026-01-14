# Advanced Features

## Typed Hooks (Enums)

Avoid magic strings by using `BackedEnum`.

```php
enum UserEvents: string {
    case Created = 'user.created';
}

Hook::addAction(UserEvents::Created, fn() => ...);
```

## Wildcard Listeners

Listen to multiple hooks using `*`.

```php
Hook::addAction('model.*.saved', function ($model) {
    // Runs for model.user.saved, model.post.saved...
});
```

## Scoped Listeners (Isolation)

Create hooks isolated to a specific object instance.

```php
$user = User::find(1);
Hook::for($user)->addFilter('avatar', fn() => 'custom.jpg');

// Only affects this user instance context
echo Hook::for($user)->applyFilters('avatar', 'default.jpg');
```

## Pipelines

For complex filter chains (Middleware style).

```php
Hook::pipe('order.process')
    ->through([
        CheckStock::class,
        ApplyDiscount::class,
    ]);
```
