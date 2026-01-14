<?php

use AlizHarb\LaravelHooks\Facades\Hook;

// 1. Wildcards
Hook::addAction('log.*', function ($msg) {
    echo "Logging: $msg\n";
});

Hook::doAction('log.error', 'Something went wrong');

// 2. Scoped
$admin = new stdClass();
Hook::for($admin)->addFilter('permission', fn() => true);

echo Hook::for($admin)->applyFilters('permission', false) ? 'Allowed' : 'Denied';
