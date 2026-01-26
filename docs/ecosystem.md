# Ecosystem & Safe DX

## Strict Signatures

Define expected arguments for hooks to prevent bugs.

```php
Hook::define('user.score', ['string', 'int']);

// Throws Exception if types mismatch
Hook::doAction('user.score', 'ali', '100'); // Error: 2nd arg must be int
```

## Deprecations

Rename hooks safely.

```php
Hook::deprecate('user.old', 'user.new', '2.0');
```

## Strict Mode

Throw an exception if a hook is called with no registered listeners. Perfect for catching typos during development.

```php
// config/hooks.php
'strict' => true,
```

## Compilation (Performance)

Production apps should compile discovery results and hook registration into the Laravel cache.

```bash
php artisan hook:cache
```

## Real-time Monitoring

Watch your hooks firing in real-time.

```bash
php artisan hook:monitor
```

## IDE Helper

Generate PHPStorm metadata for full hook name autocompletion.

```bash
php artisan hook:ide-helper
```
