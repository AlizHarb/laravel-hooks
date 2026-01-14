# Async & Queued Actions

## Concept

Sometimes you want to run an action without blocking the user request (e.g., sending emails, external API calls).
Laravel Hooks provides built-in support for dispatching actions to the Laravel Event/Job Queue.

## Usage

Use `Hook::queueAction` instead of `doAction`.

```php
// Dispatch to queue
Hook::queueAction('email.send', $user, $message);
```

This will automatically dispatch a `ProcessHookJob` behind the scenes.

## Configuration

You can configure the queue connection and queue name in your job processing settings, efficiently handled by Laravel's worker system.

## Performance

Queued actions are serializable. Ensure your arguments (like Models) are serializable or standard scalars.
