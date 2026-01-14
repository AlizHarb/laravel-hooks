# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-01-14

### Added

- **Core System**: `HookManager`, `Hook` Facade, `Actions`, `Filters`.
- **Attributes**: `#[HookAction]`, `#[HookFilter]` for PHP 8.5 attribute-based registration.
- **Advanced Features**:
  - **Typed Hooks**: Support for `BackedEnum`.
  - **Wildcards**: Pattern matching listeners (`user.*`).
  - **Scoped Hooks**: Instance-isolated hooks via `Hook::for($scope)`.
  - **Async**: `Hook::queueAction` for offloading logic to Laravel Queue.
- **Ecosystem**:
  - **Strict Signatures**: `Hook::define(...)` for type validation.
  - **Deprecations**: `Hook::deprecate(...)` with warning logs.
  - **Compilation**: `hook:cache` for static PHP performance optimization.
  - **IDE Helper**: `hook:ide-helper` for autocomplete.
- **Integrations**:
  - **Blade**: `@hook`, `@filter`, View Overrides.
  - **Debugbar**: `laravel-debugbar` DataCollector.
  - **Eloquent**: Logic to bridge model events to hooks.
- **CLI**: `list`, `inspect`, `cache`, `clear`, `ide-helper` commands.
