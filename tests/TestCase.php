<?php

declare(strict_types=1);

namespace Tests;

use AlizHarb\LaravelHooks\Facades\Hook;
use AlizHarb\LaravelHooks\HookServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            HookServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Hook' => Hook::class,
        ];
    }
}
