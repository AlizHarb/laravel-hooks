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

Compile hooks for production.

```bash
php artisan hook:cache
```

## Clear Cache

```bash
php artisan hook:clear
```

## IDE Helper

Generate metadata for IDEs.

```bash
php artisan hook:ide-helper
```
