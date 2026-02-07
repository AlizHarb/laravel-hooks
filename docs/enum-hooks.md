# Enum Hooks

Laravel Hooks 1.2.1 introduces first-class support for PHP Enums. By using the `HasEnumHooks` trait, you can make your Enums easily extendable via filters and actions.

## Usage

Add the `AlizHarb\LaravelHooks\Traits\HasEnumHooks` trait to your Backed Enums.

```php
namespace App\Enums;

use AlizHarb\LaravelHooks\Traits\HasEnumHooks;

enum OrderStatus: string
{
    use HasEnumHooks;

    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';

    public function label(): string
    {
        return $this->filter('label', match($this) {
            self::Pending => 'Waiting for Payment',
            self::Processing => 'In Progress',
            self::Completed => 'Order Finished',
        });
    }
}
```

## Hook Patterns

### Filters
The `filter(string $suffix, mixed $value, ...$args)` method triggers a filter with the following name pattern:
`enum.{sanitized_fqn}.{case_value}.{suffix}`

**Example:**
To modify the label of the `Pending` order status:
```php
Hook::addFilter('enum.app.enums.orderstatus.pending.label', fn() => '💰 Payment Required');
```

### Actions
The `action(string $suffix, ...$args)` method triggers an action:
`enum.{sanitized_fqn}.{case_value}.{suffix}`

**Example:**
To trigger a notification when a status is used:
```php
OrderStatus::Completed->action('notified', $user);

// Listener
Hook::addAction('enum.app.enums.orderstatus.completed.notified', function($status, $user) {
    // Send email...
});
```

## Built-in Helpers

### `label()`
The trait provides a default `label()` implementation that uses the `label` suffix filter. If your enum has a `label` property or you want to override the default name reflection, it will use that as the default value.
