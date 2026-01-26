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

## Conditional Hooks

You can register hooks that only activate based on runtime conditions.

```php
Hook::addAction('init', [MyListener::class, 'handle'])
    ->when(fn() => auth()->check())
    ->onlyInEnvironment('production');
```

## Strict Mode

Catch typos during development by enabling strict mode in `config/hooks.php`. When enabled, the package will throw a `HookNotFoundException` if a hook is triggered but has no listeners.

```php
'strict' => true,
```

## Eloquent Bridge

The package can automatically convert Eloquent model events into hooks. This is highly configurable in `config/hooks.php`.

```php
'eloquent_bridge' => [
    'enabled' => true,
    'events' => ['eloquent.saved*', 'eloquent.created*'],
    'models' => ['App\Models\User'],
],
```

When bridged, you can listen to hooks like:

- `eloquent.saved: App\Models\User` (Exact Laravel event)
- `model.user.saved` (Synthesized hook name for easier targeting)

## Global Monitoring

You can catch every hook fired in the system using `Hook::onAny()`. This is useful for auditing or logging.

```php
Hook::onAny(function (string $hook, array $args) {
    Log::debug("Hook fired: {$hook}");
});
```

## Real-time Monitoring (CLI)

Debug your application's logic by watching hooks fire in real-time.

```bash
php artisan hook:monitor
```

You can also filter by hook name:

```bash
php artisan hook:monitor --filter=user
```

Hook::doAction('payment.processed', $order)->graceful();
```

## Circuit Breaker

The Circuit Breaker prevents failing hook listeners from repetitively impacting application stability within a single request. If a hook fails a specific number of times (default 5), it is automatically silenced.

You can configure the threshold in `config/hooks.php`:

```php
'circuit_breaker' => [
    'enabled' => true,
    'threshold' => 5,
],
```

## Type-Safe DTOs (Signatures)

You can enforce strict typing for hook arguments using `Hook::define()`. In v1.2.0, this now supports class/interface type checking for DTO patterns.

```php
// Define the contract
Hook::define('user.registered', ['App\DTOs\UserData']);

// This will throw HookSignatureMismatchException if not an instance of UserData
Hook::doAction('user.registered', $userData);
```

## Traits

### `HasHooks` (Eloquent)

Add to your models to have model-prefixed hooks automatically.

```php
use AlizHarb\LaravelHooks\Traits\HasHooks;

class User extends Model
{
    use HasHooks;

    public function someMethod() 
    {
        // Fires 'model.user.custom_action'
        $this->fieldAction('custom_action');
    }
}
```

### `InteractsWithHooks` (Filament)

Use this in your Filament Resources, Pages, or Widgets to easily apply filters.

```php
use AlizHarb\LaravelHooks\Traits\InteractsWithHooks;

class UserResource extends Resource
{
    use InteractsWithHooks;

    public static function table(Table $table): Table
    {
        return static::applyTableHooks(
            $table->columns([ ... ])
        );
    }
}
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
