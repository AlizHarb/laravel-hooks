# Integrations

## Laravel Debugbar

If `barryvdh/laravel-debugbar` is installed, a **Hooks** tab automatically appears.
It shows:

- All executed hooks
- Memory usage
- Duration
- Arguments

## Laravel Pulse

Full performance monitoring via the `HookPulseRecorder`. Tracks hits, average duration, and memory usage per hook.

## Eloquent Integration

Automatically map Eloquent events to hooks. Use the `HasHooks` trait on your models for cleaner usage.

```php
// config/hooks.php
'eloquent_bridge' => [
    'enabled' => true,
    'except_models' => [ ... ],
],

// Usage in model
use AlizHarb\LaravelHooks\Traits\HasHooks;

class Post extends Model {
    use HasHooks;
    
    // Fires 'model.post.published'
    $this->fieldAction('published');
}
```

## Filament Bridge (v4/v5)

Extend your Filament resources modularly using the `InteractsWithHooks` trait.

```php
use AlizHarb\LaravelHooks\Traits\InteractsWithHooks;

class OrderResource extends Resource {
    use InteractsWithHooks;

    public static function table(Table $table): Table {
        return static::applyTableHooks($table);
    }
}
```

@filter('content', $content)
```

## Modular Applications

If you are using [**laravel-modular**](https://github.com/AlizHarb/laravel-modular), you can enable automatic hook discovery across all your modules by updating the `scan_paths` in `config/hooks.php`:

```php
'scan_paths' => [
    app_path('Hooks'),
    function_exists('modules_path') ? modules_path() : null,
],
```

This ensures that every `Hooks/` and `Listeners/` directory within your modules is scanned for `#[HookAction]` or `#[HookFilter]` attributes.
