# Laravel Hooks

[![Latest Version on Packagist](https://img.shields.io/packagist/v/alizharb/laravel-hooks.svg?style=flat-square)](https://packagist.org/packages/alizharb/laravel-hooks)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/AlizHarb/laravel-hooks/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/AlizHarb/laravel-hooks/actions?query=workflow%3Atests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/AlizHarb/laravel-hooks/pint.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/AlizHarb/laravel-hooks/actions?query=workflow%3APint+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/alizharb/laravel-hooks.svg?style=flat-square)](https://packagist.org/packages/alizharb/laravel-hooks)
[![License](https://img.shields.io/packagist/l/alizharb/laravel-hooks.svg?style=flat-square)](https://packagist.org/packages/alizharb/laravel-hooks)
[![PHP Stan](https://img.shields.io/github/actions/workflow/status/AlizHarb/laravel-hooks/phpstan.yml?branch=main&label=PHPStan&style=flat-square)](https://github.com/AlizHarb/laravel-hooks/actions?query=workflow%3APHPStan+branch%3Amain)

**Laravel Hooks** is a production-ready, universal extensibility system for **Laravel 12**. Inspired by WordPress but modernized with **PHP 8.5 attributes**, **Strict Typing**, and **Deep Laravel Integration**.

## ✨ Features

- ⚡ **Actions & Filters**: High-performance hook system (`addAction`, `applyFilters`).
- 🧬 **Model Extensions**: Declarative model extensions via Attributes or `Hook::model()` API.
- 🛡️ **Type-Safe**: Strict signatures contracts, `BackedEnum`, and DTO support.
- 🚦 **Circuit Breaker**: request-level failure protection for critical hooks.
- 🚀 **Async & Queued**: Dispatch heavy actions to Laravel Queue via `Hook::queueAction`.
- 🔍 **Inspector & Debugbar**: Real-time profiling with `laravel-debugbar` integration.
- 📊 **Pulse Integration**: Performance monitoring and hit counts via Laravel Pulse.
- 📦 **Ecosystem Ready**: Pipelines, Deprecations, IDE Help, and Auto-Docs generator.

## 📦 Installation

```bash
composer require alizharb/laravel-hooks
```

## 📚 Documentation

- [**Basics**](docs/basics.md): Actions, Filters, Priorities, and Attributes.
- [**Advanced Features**](docs/advanced.md): Typed Hooks, Wildcards, Scopes, Pipelines.
- [**Async & Queue**](docs/async.md): Background processing.
- [**Ecosystem & Safe DX**](docs/ecosystem.md): Signatures, Deprecations, IDE Helper, Compilation.
- [**Integrations**](docs/integrations.md): Debugbar, Eloquent, Blade.
- [**CLI Tooling**](docs/cli.md): Artisan commands.

## 🚀 Quick Start

### Basic Usage

```php
use AlizHarb\LaravelHooks\Facades\Hook;

// Register
Hook::addAction('order.created', function ($order) {
    Log::info("Order #{$order->id} created");
});

// Dispatch
Hook::doAction('order.created', $order);
```

### Attribute Registration

```php
use AlizHarb\LaravelHooks\Attributes\HookFilter;

class contentModifier
{
    #[HookFilter('content.render', priority: 20)]
    public function addSignature($content)
    {
        return $content . "\n\n-- Sent via Laravel Hooks";
    }
}
```

## 🧪 Testing

Run the test suite:

```bash
composer test
```

## 🌏 Ecosystem

Laravel Hooks is part of a larger ecosystem designed for modular, extensible Laravel applications:

- [**Laravel Modular**](https://github.com/AlizHarb/laravel-modular): Build modular Laravel apps with zero configuration.
- [**Laravel Themer**](https://github.com/AlizHarb/laravel-themer): A powerful theme engine for Laravel applications.
- [**Filament Modular Luncher**](https://github.com/AlizHarb/filament-modular-luncher): The ultimate dashboard launcher for modular projects.
- [**Filament Themer Luncher**](https://github.com/AlizHarb/filament-themer-luncher): Professional theme switching for Filament.
- [**Modular JS**](https://github.com/AlizHarb/laravel-modular-js): Seamless asset management for Laravel modules.
- First-class bridges for [**Filament**](https://github.com/AlizHarb/laravel-modular-filament) and [**Livewire**](https://github.com/AlizHarb/laravel-modular-livewire).

## 💖 Sponsors

If you find this package useful, please consider supporting its development:

- [**GitHub Sponsors**](https://github.com/sponsors/AlizHarb)

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

---

<div align="center">

**Made with ❤️ by [Ali Harb](https://github.com/AlizHarb)**

</div>
