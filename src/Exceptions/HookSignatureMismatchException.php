<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Exceptions;

use Exception;

class HookSignatureMismatchException extends Exception
{
    public static function make(string $hook, int $expected, int $received, string $type): self
    {
        return new self("Hook [{$hook}] expects argument at index {$expected} to be of type {$type}, but received different type/value.");
    }
}
