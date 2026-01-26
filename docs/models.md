# Dynamic Model Extensions

The `Hook::model()` API allows you to extend Eloquent models without modifying their source code. This is particularly useful for modular applications or packages that need to inject logic into core models.

## Basic Usage

Initiate the model extension builder using `Hook::model(Class::class)`.

```php
use AlizHarb\LaravelHooks\Facades\Hook;
use App\Models\User;

Hook::model(User::class)
    ->relation('posts', fn ($user) => $user->hasMany(Post::class))
    ->scope('active', fn ($query) => $query->where('active', true))
    ->on('created', fn ($user) => Log::info("User {$user->id} joined."))
    ->cast('meta', 'array');
```

## Attribute-based Extensions

You can also define model extensions declaratively using PHP 8.5 attributes. When you use the `HasDynamicHooks` trait, the model will automatically register these components during the boot process.

```php
use AlizHarb\LaravelHooks\Attributes\Model\{DynamicRelation, DynamicCast, DynamicScope, DynamicAccessor};
use AlizHarb\LaravelHooks\Traits\HasDynamicHooks;

#[DynamicRelation(name: 'posts', type: 'hasMany', related: Post::class)]
#[DynamicCast(attribute: 'config', type: 'array')]
#[DynamicScope(name: 'active')]
class User extends Model
{
    use HasDynamicHooks;

    #[DynamicAccessor(name: 'full_name')]
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
```

## Available Features

### Dynamic Relations

Add any relationship type to your model.

```php
Hook::model(User::class)->relation('profile', function ($user) {
    return $user->hasOne(Profile::class);
});
```

### Dynamic Scopes

Add instance-level or global scopes.

```php
// Instance Scope
Hook::model(User::class)->scope('verified', fn ($query) => $query->whereNotNull('email_verified_at'));

// Global Scope
Hook::model(User::class)->globalScope('tenant', function ($query) {
    $query->where('tenant_id', auth()->user()->tenant_id);
});
```

### Model Events

Register class-level observers for model lifecycle events.

```php
Hook::model(User::class)->on('saving', function ($user) {
    $user->slug = Str::slug($user->name);
});
```

### Dynamic Casts

Inject attribute casting at runtime.

```php
Hook::model(User::class)->cast('last_login_at', 'datetime');
```

### Accessors & Mutators

Add virtual attributes or modify existing ones.

```php
// Simple Accessor (injected as a runtime relation for dot-notation)
Hook::model(User::class)->accessor('full_name', fn ($user) => "{$user->first_name} {$user->last_name}");

// Comprehensive Attribute (Getter/Setter)
Hook::model(User::class)->attribute(
    'slug',
    get: fn ($user) => Str::slug($user->name),
    set: fn ($user, $value) => $user->name = str_replace('-', ' ', $value)
);

### Fillable, Guarded & Hidden
Modify model protection and visibility at runtime.

```php
Hook::model(User::class)
    ->fillable(['bio', 'avatar'])
    ->hidden(['password_hash', 'api_token'])
    ->visible(['public_email']);
```
```

---

## Deep Extensibility with `HasDynamicHooks`

For even deeper integration, add the `AlizHarb\LaravelHooks\Traits\HasDynamicHooks` trait to your model.

### Dynamic Methods

Once the trait is added, you can add any method to your model class.

```php
// In Model:
use AlizHarb\LaravelHooks\Traits\HasDynamicHooks;

class User extends Model {
    use HasDynamicHooks;
}

// Extension:
Hook::model(User::class)->method('getGreeting', function () {
    return "Hello, " . $this->name;
});

// Usage:
$user->getGreeting();
```

### Property Filtering

The trait intercepts `getAttribute()` calls, allowing you to filter any property value via the standard hook system.

```php
// Filter any attribute access
Hook::addFilter('model.user.attribute.email', function ($value) {
    return strtolower($value);
});

// Filter attribute assignment
Hook::addFilter('model.user.set_attribute.password', function ($value) {
    return Hash::make($value);
});

// Filter serialized array (toArray)
Hook::addFilter('model.user.to_array', function ($array, $user) {
    $array['generated_at'] = now()->toDateTimeString();
    return $array;
});
```
