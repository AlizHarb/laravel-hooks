<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Traits;

use AlizHarb\LaravelHooks\Facades\Hook;

/**
 * Trait HasEnumHooks.
 *
 * Add this trait to your PHP Backed Enums to make them hookable.
 */
trait HasEnumHooks
{
    /**
     * Filter a value associated with this enum case.
     */
    public function filter(string $suffix, mixed $value, mixed ...$args): mixed
    {
        $name = str_replace('\\', '.', strtolower(static::class));
        $hook = "enum.{$name}.{$this->value}.{$suffix}";

        return Hook::applyFilters($hook, $value, $this, ...$args);
    }

    /**
     * Dispatch an action associated with this enum case.
     */
    public function action(string $suffix, mixed ...$args): void
    {
        $name = str_replace('\\', '.', strtolower(static::class));
        $hook = "enum.{$name}.{$this->value}.{$suffix}";

        Hook::doAction($hook, $this, ...$args);
    }

    /**
     * Get the labeled value for the enum, allowing hooks to translate or modify it.
     */
    public function label(): string
    {
        $default = property_exists($this, 'label') ? $this->label : $this->name;

        return (string) $this->filter('label', $default);
    }

    /**
     * Get all cases, including virtual ones registered via Hook system.
     *
     * @return array<int, static|object>
     */
    public static function all(): array
    {
        $native = static::cases();
        $virtual = Hook::getEnumCases(static::class);

        $mappedVirtual = [];
        foreach ($virtual as $case) {
            $mappedVirtual[] = new class($case['name'], $case['value'], static::class)
            {
                public function __construct(
                    public string $name,
                    public mixed $value,
                    protected string $enumClass
                ) {}

                public function filter(string $suffix, mixed $value, mixed ...$args): mixed
                {
                    $name = str_replace('\\', '.', strtolower($this->enumClass));
                    $hook = "enum.{$name}.{$this->value}.{$suffix}";

                    return Hook::applyFilters($hook, $value, $this, ...$args);
                }

                public function action(string $suffix, mixed ...$args): void
                {
                    $name = str_replace('\\', '.', strtolower($this->enumClass));
                    $hook = "enum.{$name}.{$this->value}.{$suffix}";

                    Hook::doAction($hook, $this, ...$args);
                }

                public function label(): string
                {
                    return (string) $this->filter('label', $this->name);
                }

                public function __toString(): string
                {
                    return (string) $this->value;
                }
            };
        }

        return array_merge($native, $mappedVirtual);
    }
}
