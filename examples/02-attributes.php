<?php

use AlizHarb\LaravelHooks\Attributes\HookAction;
use AlizHarb\LaravelHooks\Attributes\HookFilter;

class UserListener
{
    #[HookAction('user.registered')]
    public function onRegister($user)
    {
        echo "Registered: {$user->name}\n";
    }

    #[HookFilter('user.name', priority: 5)]
    public function formatName($name)
    {
        return ucfirst($name);
    }
}
