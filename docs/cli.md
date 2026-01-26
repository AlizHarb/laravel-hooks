# CLI Tooling

## List Hooks

View all registered hooks, their priorities, and callback types.

```bash
php artisan hook:list
```

## Inspect Hook

Debug a specific hook to see who is listening.

```bash
php artisan hook:inspect user.created
```

## Cache

Compile hooks for production. This will trigger the **Discovery Engine** to scan your `scan_paths`, register all found hooks via attributes, and store the final map in the Laravel cache.

```bash
php artisan hook:cache
```

## Clear Cache

Removes the compiled hook map from the cache.

```bash
php artisan hook:clear
```

## IDE Helper

Generate dynamic PHPStorm metadata for IDEs. This command fetches all registered and discovered hook names to provide full autocompletion when using `Hook::doAction()` or `Hook::applyFilters()`.

```bash
php artisan hook:ide-helper
```
