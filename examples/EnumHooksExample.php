<?php

declare(strict_types=1);

namespace Examples;

use AlizHarb\LaravelHooks\Facades\Hook;
use AlizHarb\LaravelHooks\Traits\HasEnumHooks;

/**
 * Example Enum: UserRole.
 */
enum UserRole: string
{
    use HasEnumHooks;

    case Admin = 'admin';
    case Editor = 'editor';
    case Subscriber = 'subscriber';

    /**
     * Get the display label for the role.
     *
     * Hook: enum.examples.userrole.{role}.label
     */
    public function getLabel(): string
    {
        return $this->filter('label', match ($this) {
            self::Admin => 'System Administrator',
            self::Editor => 'Content Editor',
            self::Subscriber => 'Standard Subscriber',
        });
    }
}

/**
 * Usage Example.
 */
class EnumExampleUsage
{
    public function run()
    {
        // 1. Basic Usage
        echo UserRole::Admin->getLabel(); // Outputs: System Administrator

        // 2. Extending via Hooks (usually in a ServiceProvider)
        Hook::addFilter('enum.examples.userrole.admin.label', function () {
            return '👑 Super Admin';
        });

        echo UserRole::Admin->getLabel(); // Outputs: 👑 Super Admin

        // 3. Conditional Labels
        Hook::addFilter('enum.examples.userrole.editor.label', function ($current) {
            // Simulated premium check
            return $current.' (Premium)';
        });

        // 4. Dynamic Enum Cases (FULL DYNAMIC)
        Hook::enum(UserRole::class)
            ->addCase('Moderator', 'mod');

        Hook::addFilter('enum.examples.userrole.mod.label', function () {
            return '🛡️ Global Moderator';
        });

        // Loop through everything (native + dynamic)
        foreach (UserRole::all() as $role) {
            echo "{$role->value}: {$role->label()}\n";
        }
    }
}
