<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Exceptions;

use Exception;

class HookNotFoundException extends Exception
{
    public static function forHook(string $hook): self
    {
        return new self("Hook [{$hook}] not found or has no registered listeners.");
    }
}
