# Context Store

Laravel Hooks 1.2.1 introduces a request-cycle **Context Store**. This allows different hooks (Models, Actions, Enums) to share state without relying on global variables or complex dependency injection.

## API

The context store is accessible via the `Hook` facade:

- `Hook::setContext(string $key, mixed $value): void`
- `Hook::getContext(string $key, mixed $default = null): mixed`
- `Hook::hasContext(string $key): bool`
- `Hook::forgetContext(string $key): void`
- `Hook::flushContext(): void`

## Real-world Example: Bridging Model and Action

Imagine you want to track which dynamic model attribute triggered a specific action.

### 1. The Model Hook
```php
Hook::addAction('model.app.models.post.saving', function($post) {
    if ($post->isDirty('title')) {
        Hook::setContext('post_edit_source', 'web_editor');
    }
});
```

### 2. The Action Listener
```php
Hook::addAction('action.app.actions.updatepost.executed', function($action, $post) {
    $source = Hook::getContext('post_edit_source', 'api');
    
    Log::info("Post {$post->id} updated via {$source}");
});
```

The context store is automatically flushed at the end of the request if using standard Laravel lifecycle (singleton registration).
