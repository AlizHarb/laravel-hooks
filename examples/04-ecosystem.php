<?php

use AlizHarb\LaravelHooks\Facades\Hook;

// 1. Strict Signatures
Hook::define('math.add', ['int', 'int']);
Hook::doAction('math.add', 1, 2); // OK

try {
    Hook::doAction('math.add', '1', '2');
} catch (Exception $e) {
    echo "Type mismatch caught!\n";
}

// 2. Deprecation
Hook::deprecate('old.event', 'new.event', '1.5');
Hook::doAction('old.event'); // Logs warning
