# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-01-26

### Added

- **Enterprise Features**
  - **Database Transaction Support**: `Hook::transactional()->doAction()`.
  - **Recursive Loop Protection**: Protect against infinite hook loops via `max_nesting`.
  - **Muting & Silencing**: `Hook::mute()`, `Hook::silence()`, and `Hook::withoutHooks()`.
- **Infrastructure**
  - **Discovery Engine**: Support for attribute discovery in **Traits** and **Classes**.
  - **Real-time CLI Monitoring**: New `php artisan hook:monitor` command.
  - **Auto Documentation Generator**: New `php artisan hook:generate-docs` command.
  - **Laravel Pulse Integration**: High-performance hook tracking and bottleneck detection.
  - **Tooling**: Added `php-cs-fixer` configuration and GitHub Action badge.
- **Bridges & Traits**
  - **Filament Bridge (v4/v5)**: support for **Schemas**, Table, Infolist, Action, Widget, and Page hooks.
  - **Livewire Bridge**: Global lifecycle event mapping for Livewire components.
  - **`HasHooks` Trait**: Simplified model-specific hooks for Eloquent.
  - **`InteractsWithHooks` Trait**: Extension point for Filament components.
  - **`InteractsWithLivewireHooks` Trait**: Extension point for Livewire components.
- **Core Improvements**
  - **Strict Mode**: `hooks.strict` config to throw exceptions for missing listeners.
  - **Conditional Hooks**: Support for `when()` and `onlyInEnvironment()` registration.
  - **Global Monitoring**: `Hook::onAny()` listener for auditing.
  - **Graceful Failures**: `Hook::gracefully()->doAction()` to prevent crash on listener error.
  - **Global Helpers**: Familiar `do_action()`, `apply_filters()`, and `hook()` functions.

### Fixed

- **Pulse Recorder**: Fixed `HookPulseRecorder` inheritance and type casting issues.
- **HookManager**: Fixed global monitor argument passing and early return logic.
- **Discovery**: Fixed `InvalidCallbackException` for array-based callbacks (`[Class::class, 'method']`).
- **Tests**: Reorganized into `Unit` and `Feature` suites with strict PSR-4 namespaces.

### Changed

- **Hook Caching**: Refined the caching system to use the discovery engine and Laravel's cache store.
- **HookManager**: Added `getRegisteredHookNames()`, `setFilters()`, and `setWildcardFilters()`.

## [1.0.0] - 2026-01-14

### Initial Release

- **Core System**: `HookManager`, `Hook` Facade, `Actions`, `Filters`.
- **Attributes**: `#[HookAction]`, `#[HookFilter]` for PHP 8 attribute-based registration.
- **Advanced Features**: Typed Hooks (Enums), Wildcards, Scoped Hooks, Async/Queued Actions.
- **Ecosystem**: Strict Signatures, Deprecations, Compilation, IDE Helper.
- **Integrations**: Blade Directives, Debugbar, Eloquent Bridge.
- **CLI**: `list`, `inspect`, `cache`, `clear`, `ide-helper` commands.
