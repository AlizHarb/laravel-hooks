<?php

declare(strict_types=1);

namespace Tests\Feature;

use AlizHarb\LaravelHooks\Facades\Hook;
use Tests\TestCase;

class ContextStoreTest extends TestCase
{
    public function test_it_can_store_and_retrieve_context(): void
    {
        Hook::setContext('test_key', 'test_value');

        $this->assertTrue(Hook::hasContext('test_key'));
        $this->assertEquals('test_value', Hook::getContext('test_key'));
    }

    public function test_it_can_forget_context(): void
    {
        Hook::setContext('temp', 123);
        Hook::forgetContext('temp');

        $this->assertFalse(Hook::hasContext('temp'));
        $this->assertNull(Hook::getContext('temp'));
    }

    public function test_it_can_flush_all_context(): void
    {
        Hook::setContext('a', 1);
        Hook::setContext('b', 2);

        Hook::flushContext();

        $this->assertFalse(Hook::hasContext('a'));
        $this->assertFalse(Hook::hasContext('b'));
    }

    public function test_it_returns_default_value_if_not_found(): void
    {
        $this->assertEquals('default', Hook::getContext('missing', 'default'));
    }
}
