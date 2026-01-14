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

## Compilation (Performance)

Production apps should cache hooks to a static PHP file.

```bash
php artisan hook:cache
```

## IDE Helper

Generate PHPStorm metadata for autocompletion.

```bash
php artisan hook:ide-helper
```
