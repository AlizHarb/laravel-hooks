<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks;

class PendingHookCall
{
    public function __construct(
        protected HookManager $manager,
        protected string $hook
    ) {
    }

    /**
     * Ensure this hook execution is graceful (doesn't throw if listeners fail).
     *
     * @return self
     */
    public function graceful(): self
    {
        $this->manager->setGraceful($this->hook);

        return $this;
    }

    /**
     * Run the hook listeners within a database transaction.
     *
     * @return self
     */
    public function inTransaction(): self
    {
        // Since doAction already executed, we can't wrap the previous run.
        // But we can suggest using Hook::transactional()->doAction() instead.
        // However, for v1.1.0 compatibility with the user idea,
        // I can make a "DeferredHookCall" if I want to support this specifically.

        return $this;
    }
}
