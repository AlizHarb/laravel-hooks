<?php

declare(strict_types=1);

namespace Tests\Feature;

use AlizHarb\LaravelHooks\Facades\Hook;
use Tests\TestCase;

class HookValidationTest extends TestCase
{
    public function test_smart_validation_filters_invalid_data(): void
    {
        // Hook that expects a string but returns an integer (invalid)
        Hook::addFilter('test.validation', function ($val) {
            return 123;
        })->validate('string');

        $result = Hook::applyFilters('test.validation', 'original');

        // Should fallback to 'original' due to validation failure
        $this->assertEquals('original', $result);
    }

    public function test_validation_with_closure(): void
    {
        Hook::addFilter('test.closure_val', function ($val) {
            return $val;
        })->validate(fn ($v) => is_numeric($v));

        $this->assertEquals(100, Hook::applyFilters('test.closure_val', 100));

        // Should fallback if closure returns false
        $this->assertEquals('fallback', Hook::applyFilters('test.closure_val', 'fallback'));
    }
}
