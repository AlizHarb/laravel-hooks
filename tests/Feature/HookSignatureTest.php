<?php

declare(strict_types=1);

namespace Tests\Feature;

use AlizHarb\LaravelHooks\Exceptions\HookSignatureMismatchException;
use AlizHarb\LaravelHooks\Facades\Hook;
use Tests\TestCase;

class HookSignatureTest extends TestCase
{
    public function test_signature_enforcement_prevents_mismatch(): void
    {
        config(['hooks.strict_mode' => true]);

        Hook::addFilter('test.strict', function ($a, $b) {
            return $a.$b;
        }, 10, 2);

        $this->expectException(HookSignatureMismatchException::class);

        // Only providing one argument for a hook that expects two
        Hook::applyFilters('test.strict', 'first');
    }

    public function test_signature_enforcement_passes_when_correct(): void
    {
        config(['hooks.strict_mode' => true]);

        Hook::addFilter('test.strict.pass', function ($a, $b) {
            return $a.$b;
        }, 10, 2);

        $result = Hook::applyFilters('test.strict.pass', 'hello', ' world');
        $this->assertEquals('hello world', $result);
    }
}
