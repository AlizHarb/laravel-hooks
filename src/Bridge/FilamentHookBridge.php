<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Bridge;

use AlizHarb\LaravelHooks\Facades\Hook;
use Filament\Infolists\Infolist;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class FilamentHookBridge
{
    /**
     * Register listeners for Filament events or hook into Filament components.
     *
     * @return void
     */
    public function register(): void
    {
        if (! class_exists(Table::class) && ! class_exists(Schema::class)) {
            return;
        }

        $config = config('hooks.filament_bridge', []);

        if (! ($config['enabled'] ?? false)) {
            return;
        }
    }

    /**
     * A helper to be called in Filament Resource's table() method.
     *
     * @param mixed $table
     * @param string|null $context
     * @return mixed
     */
    public static function applyTableHooks(mixed $table, ?string $context = null): mixed
    {
        if (method_exists($table, 'getLivewire')) {
            $context ??= class_basename($table->getLivewire());
        }

        return Hook::applyFilters("filament.table.{$context}", $table);
    }

    /**
     * A helper to be called in Filament Resource's form() method.
     *
     * @param mixed $schema
     * @param string|null $context
     * @return mixed
     */
    public static function applySchemaHooks(mixed $schema, ?string $context = null): mixed
    {
        if (method_exists($schema, 'getLivewire')) {
            $context ??= class_basename($schema->getLivewire());
        }

        return Hook::applyFilters("filament.schema.{$context}", $schema);
    }

    /**
     * A helper to be called in Filament Resource's infolist() method.
     *
     * @param mixed $infolist
     * @param string|null $context
     * @return mixed
     */
    public static function applyInfolistHooks(mixed $infolist, ?string $context = null): mixed
    {
        if (method_exists($infolist, 'getLivewire')) {
            $context ??= class_basename($infolist->getLivewire());
        }

        return Hook::applyFilters("filament.infolist.{$context}", $infolist);
    }

    /**
     * A helper to be called in Filament Page's configuration.
     *
     * @param mixed $page
     * @param string|null $context
     * @return mixed
     */
    public static function applyPageHooks(mixed $page, ?string $context = null): mixed
    {
        $context ??= is_object($page) ? class_basename($page) : (string) $page;

        return Hook::applyFilters("filament.page.{$context}", $page);
    }

    /**
     * A helper to be called in Filament Widget's configuration.
     *
     * @param mixed $widget
     * @param string|null $context
     * @return mixed
     */
    public static function applyWidgetHooks(mixed $widget, ?string $context = null): mixed
    {
        $context ??= is_object($widget) ? class_basename($widget) : (string) $widget;

        return Hook::applyFilters("filament.widget.{$context}", $widget);
    }

    /**
     * A helper to be called in Filament RelationManager's configuration.
     *
     * @param mixed $manager
     * @param string|null $context
     * @return mixed
     */
    public static function applyRelationManagerHooks(mixed $manager, ?string $context = null): mixed
    {
        $context ??= is_object($manager) ? class_basename($manager) : (string) $manager;

        return Hook::applyFilters("filament.relation_manager.{$context}", $manager);
    }

    /**
     * A helper to be called for Filament Actions.
     *
     * @param mixed $action
     * @param string|null $context
     * @return mixed
     */
    public static function applyActionHooks(mixed $action, ?string $context = null): mixed
    {
        $context ??= is_object($action) ? class_basename($action) : (string) $action;

        return Hook::applyFilters("filament.action.{$context}", $action);
    }
}
