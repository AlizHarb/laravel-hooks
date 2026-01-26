<?php

use AlizHarb\LaravelHooks\Facades\Hook;

/**
 * 1. WILDCARD HOOKS
 * Match multiple tags using '*' pattern.
 */
Hook::addAction('order.*', function ($order) {
    // Audit every single order event
    echo "Something happened to order #{$order->id}...\n";
});


/**
 * 2. CONDITIONAL REGISTRATION
 * Register hooks that only active if conditions are met.
 */
Hook::addFilter('api.response', fn ($res) => array_merge($res, ['debug' => true]))
    ->when(fn () => config('app.debug'))
    ->onlyInEnvironment('local');


/**
 * 3. SCOPED LISTENERS
 * Isolated hooks for specific object instances.
 */
$payment = new stdClass();
$payment->id = 123;

Hook::for($payment)->addFilter('status_label', fn () => 'Success (Override)');

// This is isolated:
echo Hook::for($payment)->applyFilters('status_label', 'Pending'); // Success (Override)
echo Hook::applyFilters('status_label', 'Pending');               // Pending


/**
 * 4. GRACEFUL EXECUTION
 * Prevent listener errors from crashing the main flow.
 */
Hook::addAction('user.login', function () {
    throw new Exception("Non-critical service down");
});

// Main process continues even if the above fails
Hook::doAction('user.login')->graceful();


/**
 * 6. TRANSACTIONAL HOOKS
 * Wrap hook execution in a DB transaction.
 */
Hook::transactional()->doAction('user.purchase', $order);


/**
 * 7. LOOP PROTECTION
 * Recursive calls are caught automatically.
 */
// This would throw if called more than once (configured via config('hooks.max_nesting'))
// Hook::addAction('ping', fn() => Hook::doAction('ping'));


/**
 * 8. DOCUMENTATION GENERATION
 * Run `php artisan hook:generate-docs` to find all hooks in your app.
 */
