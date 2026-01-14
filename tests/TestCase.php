<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use AlizHarb\LaravelHooks\HookServiceProvider;
use AlizHarb\LaravelHooks\Facades\Hook;

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
