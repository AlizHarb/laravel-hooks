<?php

use AlizHarb\LaravelHooks\Facades\Hook;

// 1. Actions
Hook::addAction('app.booted', function () {
    echo "App Booted!\n";
});

Hook::doAction('app.booted');

// 2. Filters
Hook::addFilter('string.upper', function ($str) {
    return strtoupper($str);
});

echo Hook::applyFilters('string.upper', 'hello world'); // HELLO WORLD
