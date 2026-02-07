# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.1] - 2026-02-07

### Added

- **Elite System Overhaul**:
  - Queued Action Hooks (`onQueue()`) for async execution via Laravel Queue.
  - Smart Result Validation (`validate()`) using Laravel Validator for hook results.
  - Framework Event Bridge (`Hook::bridge()`) to map native Laravel events to hooks.
  - Real-time `hook:trace` Artisan command for monitoring mutations.
  - Enhanced Hook Signature Enforcement for argument count and type safety.
  - Core Recursion Protection in `HasDynamicHooks` magic methods.
- **Universal Enum Hooks**: New `HasEnumHooks` trait for PHP Enums to filter labels and values.
- **Hookable Actions**: New `MakesActionsHookable` trait for Action/Service classes.
- **Multi-line Blade Fallbacks**: support for default content blocks via `@hookBlock`.

### Fixed

- **Dynamic Scopes**: Fixed Eloquent dynamic scope registration to correctly integrate with the Builder.
- **Hook Arguments**: Corrected missing `$model` argument pass-through in relation, accessor, and attribute hooks.
- **Static Analysis**: Resolved PHPStan false positives and unused variable warnings in `ModelHookBuilder`.
- **Code Style**: Aligned codebase with Laravel Pint standards and resolved configuration conflicts.
- **Memory Exhaustion**: Resolved critical infinite recursion in Eloquent magic methods.
- **Blade Directives**: Fixed `@hook` to correctly support passing arguments and fallback views.
- **Model Hook Naming**: Standardized model hooks to use `class_basename` for compatibility.

### Improved

- **Metadata Performance**: Added static caching to `HasDynamicHooks`.
- **Blade DX**: Added `renderAction` helper for cleaner integration.

## [1.2.0] - 2026-01-31

### Added

- Smart Hook Discovery via PHP 8 Attributes.
- Scoped Hook Management (local vs global).
- Improved Exception handling and DX.

## [1.1.4] - 2026-01-27

### Initial Stable Release

- Core Hook/Filter system.
- Blade directive support.
- Basic Eloquent integration.
