<?php

use AlizHarb\LaravelHooks\Facades\Hook;

/**
 * --------------------------------------------------------------------------
 * CORE ACTIONS
 * Actions allow you to "do something" at a specific point in execution.
 * --------------------------------------------------------------------------
 */

// Simple action registration
Hook::addAction('order.completed', function ($orderId) {
    // Send notification to customer
    echo "Order #{$orderId} completed! Sending email...\n";
});

// Priority-based execution (Lower numbers run first)
Hook::addAction('order.completed', function ($orderId) {
    // Log for auditing
    echo "Auditing order #{$orderId}...\n";
}, 5);

// Execute the action
Hook::doAction('order.completed', 1024);


/**
 * --------------------------------------------------------------------------
 * CORE FILTERS
 * Filters allow you to intercept and modify data.
 * --------------------------------------------------------------------------
 */

// Basic data modification
Hook::addFilter('user.profile_bio', function ($bio) {
    return strip_tags($bio);
});

// Chained modifications
Hook::addFilter('user.profile_bio', function ($bio) {
    return trim($bio) . " (Verified Member)";
}, 20);

// Apply filters to a value
$rawBio = "  <script>alert('xss')</script> Hello there!   ";
$finalBio = Hook::applyFilters('user.profile_bio', $rawBio);

echo "Final Bio: {$finalBio}\n"; // Hello there! (Verified Member)
