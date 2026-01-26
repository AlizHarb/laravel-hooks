# Installation

Installing Laravel Hooks is simple via Composer.

## Requirements

- PHP 8.3 or higher
- Laravel 11.0 or higher

## Install via Composer

```bash
composer require alizharb/laravel-hooks
```

The package will automatically register its ServiceProvider and Facade.

## Configuration

Optionally publish the configuration file to customize cache settings and Eloquent bridging.

```bash
php artisan vendor:publish --tag=hooks-config
```

> [!TIP]
> Make sure to configure the `scan_paths` in your `config/hooks.php` if your hooks are located outside the default `app/Hooks` or `app/Listeners` directories.

## IDE Helper

To improve your development experience with autocompletion for hooks, generate the metadata file:

```bash
php artisan hook:ide-helper
```
