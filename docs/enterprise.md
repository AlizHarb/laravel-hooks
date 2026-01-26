# Enterprise Features

Laravel Hooks v1.1.0 includes enterprise-grade features for high-scale, modular applications.

## Recursive Loop Protection

To prevent infinite loops (e.g., a listener on `save` updates the model and triggers `save` again), the package automatically tracks nesting depth.

```php
// config/hooks.php
'max_nesting' => 50,
```

If a hook exceeds this limit, a `RuntimeException` is thrown.

## Database Transaction Support

You can ensure all listeners for an action run within a database transaction. If any listener fails, the transaction is rolled back.

```php
Hook::transactional()->doAction('order.process', $order);
```

## Dynamic Hook Documentation

In large modular systems, it can be hard to know what hooks are available. Use the generator to create a `HOOKS.md` file:

```bash
php artisan hook:generate-docs
```

This scans your `app` directory for `doAction` and `applyFilters` calls and generates a markdown table.

## Laravel Pulse Integration

Monitor hook performance and usage statistics in real-time. If you have Laravel Pulse installed, metrics are automatically recorded.

- **Slowest Hooks**: Identify performance bottlenecks.
- **Hook Hits**: See which hooks are triggered most frequently.
- **Memory usage**: Track the memory footprint of your hook chain.

## Muting & Silencing

Useful for tests or background jobs where you want to perform actions without triggering modular side-effects.

```php
// Mute specific hook
Hook::mute('notifications.send');

// Silence everything
Hook::silence();

// Execute callback without hooks
Hook::withoutHooks(function () {
    // No hooks will fire here
});
```

## Livewire 4 Integration

Extend your reactive components modularly using global event bridging or the specialized trait.

### Bridge Approach (Global)

Enable `livewire_bridge` in config. This automatically maps Livewire lifecycle events to hooks:
- `livewire.{ComponentName}.mount`
- `livewire.mount` (for all components)

### Trait Approach (Explicit)

Use the `InteractsWithLivewireHooks` trait for fine-grained control:

```php
class PostList extends Component {
    use InteractsWithLivewireHooks;
    
    // Fires 'livewire.PostList.updating.search' automatically
}
```

## Global Helper Functions

For developers who prefer a WordPress-style API, v1.1.0 includes global helpers:

```php
// Dispatch action
do_action('user.login', $user);

// Apply filters
$title = apply_filters('post.title', $title);

// Fluent access
hook()->mute('debug.*');
```
