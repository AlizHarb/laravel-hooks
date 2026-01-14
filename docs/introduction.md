# Introduction

**Laravel Hooks** is a lightweight, high-performance universal extensibility system for Laravel applications. Inspired by the WordPress hook system but modernized for PHP 8.5+, it allows you to build modular, plugin-friendly architectures with ease.

## Why Laravel Hooks?

Laravel's Event system is powerful but can be heavy for simple data modification (filters). Laravel Hooks fills this gap by offering:

- **Filters**: Modify data flowing through your application (e.g., change a string, update an array).
- **Actions**: Fire custom events at specific points in your code.
- **Developer Experience**: Use attributes `#[HookAction]` or standard Facade methods.
- **Performance**: Compiled to static PHP for near-zero overhead in production.

## Key Features

- ⚡ **Zero Dependencies**: Pure PHP implementation.
- 🧬 **Attributes**: PHP 8.5+ native attributes support.
- 🛡️ **Type Safety**: strict signatures and Enums.
- 🔮 **Debugbar Integration**: Visualize all hooks in Laravel Debugbar.
- 📦 **Blade Directives**: `@hook` and `@filter` for your views.
