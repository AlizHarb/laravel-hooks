<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Exceptions;

use Exception;

class HookNotFoundException extends Exception
{
    /**
     * Create a new HookNotFoundException instance.
     *
     * @param string $hook
     * @return self
     */
    public static function make(string $hook): self
    {
        return new self("Hook [{$hook}] has no registered listeners and strict mode is enabled.");
    }
}
