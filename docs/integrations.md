# Integrations

## Laravel Debugbar

If `barryvdh/laravel-debugbar` is installed, a **Hooks** tab automatically appears.
It shows:

- All executed hooks
- Memory usage
- Duration
- Arguments

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

## Blade Directives

```blade
@hook('header')

@filter('content', $content)
```
