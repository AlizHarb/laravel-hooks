<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Exceptions;

use Exception;

class InvalidCallbackException extends Exception
{
    public static function notCallable(string $hook): self
    {
        return new self("Invalid callback provided for hook [{$hook}]. Callback must be callable or a valid class@method string.");
    }
}
