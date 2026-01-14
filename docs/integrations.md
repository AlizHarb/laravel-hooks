# Integrations

## Laravel Debugbar

If `barryvdh/laravel-debugbar` is installed, a **Hooks** tab automatically appears.
It shows:

- All executed hooks
- Memory usage
- Duration
- Arguments

## Eloquent Bridge

Automatically map Eloquent events to hooks.
Enable `eloquent_bridge` in `config/hooks.php`.

```php
// Listen to any model event
Hook::addAction('eloquent.saved: App\Models\User', function ($user) {
    // ...
});
```

## Blade Directives

```blade
@hook('header')

@filter('content', $content)
```
