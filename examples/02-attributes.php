<?php

namespace App\Listeners;

use AlizHarb\LaravelHooks\Attributes\HookAction;
use AlizHarb\LaravelHooks\Attributes\HookFilter;

/**
 * --------------------------------------------------------------------------
 * ATTRIBUTE-BASED REGISTRATION
 * The package automatically discovers these in your scan_paths.
 * --------------------------------------------------------------------------
 */

class AnalyticsSubscriber
{
    /**
     * Listeners marked with #[HookAction] are registered as actions.
     */
    #[HookAction(hook: 'video.started', priority: 5)]
    public function logPlay($videoId, $userId): void
    {
        // Log to database or external service
        echo "User #{$userId} started watching video #{$videoId}\n";
    }

    /**
     * Listeners marked with #[HookFilter] are registered as filters.
     */
    #[HookFilter(hook: 'search.results', acceptedArgs: 2)]
    public function filterSearchResults(array $results, array $criteria): array
    {
        if (isset($criteria['exclude_hidden'])) {
            return array_filter($results, fn ($r) => ! $r['is_hidden']);
        }

        return $results;
    }
}

// In production, run 'php artisan hook:cache' to compile these into a high-performance map.
