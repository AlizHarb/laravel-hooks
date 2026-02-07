<?php

declare(strict_types=1);

namespace Tests\Feature;

use AlizHarb\LaravelHooks\Facades\Hook;
use AlizHarb\LaravelHooks\Traits\HasEnumHooks;
use Tests\TestCase;

enum Status: string
{
    use HasEnumHooks;

    case Active = 'active';
    case Inactive = 'inactive';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active User',
            self::Inactive => 'Deactivated',
        };
    }
}

class EnumHookTest extends TestCase
{
    public function test_enum_can_be_filtered(): void
    {
        $status = Status::Active;

        Hook::addFilter('enum.tests.feature.status.active.label', fn () => 'Filtered Label');

        $this->assertEquals('Filtered Label', $status->label());
    }

    public function test_enum_falls_back_to_name_if_no_label_property(): void
    {
        $status = Status::Inactive;
        $this->assertEquals('Inactive', $status->label());
    }

    public function test_enum_can_dispatch_actions(): void
    {
        $executed = false;
        Hook::addAction('enum.tests.feature.status.active.updated', function () use (&$executed) {
            $executed = true;
        });

        Status::Active->action('updated');

        $this->assertTrue($executed);
    }

    public function test_enum_can_have_dynamic_cases(): void
    {
        Hook::enum(Status::class)->addCase('Archived', 'archived');

        $all = Status::all();

        $this->assertCount(3, $all); // Active, Inactive, Archived

        $archived = $all[2];
        $this->assertEquals('Archived', $archived->name);
        $this->assertEquals('archived', $archived->value);
        $this->assertEquals('Archived', $archived->label());

        // Test filtering on dynamic case
        Hook::addFilter('enum.tests.feature.status.archived.label', fn () => '📦 Archived');
        $this->assertEquals('📦 Archived', $archived->label());
    }
}
