# Action Hooks

Laravel Hooks 1.2.1 adds the `MakesActionsHookable` trait, designed for logic-heavy "Action" or "Service" classes. This allows developers to hook into the lifecycle of an action without modifying its core logic.

## Usage

Add `AlizHarb\LaravelHooks\Traits\MakesActionsHookable` to your class and wrap your logic in `executeWithHooks`.

```php
namespace App\Actions;

use AlizHarb\LaravelHooks\Traits\MakesActionsHookable;

class CreateUser
{
    use MakesActionsHookable;

    public function handle(array $data)
    {
        return $this->executeWithHooks(function($data) {
            return User::create($data);
        }, $data);
    }
}
```

## Lifecycle Hooks

The trait automatically fires three types of hooks:

1. **Executing**: `action.{sanitized_fqn}.executing`
   - Triggered before the logic runs.
   - Receives: `($this, ...$args)`

2. **Executed**: `action.{sanitized_fqn}.executed`
   - Triggered after the logic completes successfully.
   - Receives: `($this, $result, ...$args)`

3. **Failed**: `action.{sanitized_fqn}.failed`
   - Triggered if an exception is thrown.
   - Receives: `($this, $exception, ...$args)`

4. **Result (Filter)**: `action.{sanitized_fqn}.result`
   - Allows modification of the return value.
   - Receives: `($result, $this, ...$args)`

## Example: Auditing an Action

```php
Hook::addAction('action.app.actions.createuser.executed', function($action, $user, $data) {
    Log::info("User created with ID: {$user->id}");
});

Hook::addFilter('action.app.actions.createuser.result', function($user) {
    $user->is_new = true;
    return $user;
});
```
