<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks;

use Closure;

class PendingHookRegistration
{
    public function __construct(
        protected HookManager $manager,
        protected string $hook,
        protected string $id,
        protected int $priority,
        protected bool $isFilter = true
    ) {}

    /**
     * Only register the hook if the condition is met.
     */
    public function when(bool|Closure $condition): self
    {
        $result = $condition instanceof Closure ? $condition() : $condition;

        if (! $result) {
            if ($this->isFilter) {
                $this->manager->removeFilter($this->hook, $this->id, $this->priority);
            } else {
                $this->manager->removeAction($this->hook, $this->id, $this->priority);
            }
        }

        return $this;
    }

    /**
     * Only register the hook if in specific environment.
     */
    public function onlyInEnvironment(string|array $environments): self
    {
        return $this->when(app()->environment($environments));
    }

    /**
     * Mark the listener to be executed on a queue.
     * Note: Does not work with Closures.
     *
     * @return $this
     */
    public function onQueue(?string $connection = null, ?string $queue = null): self
    {
        $filters = $this->manager->getFilters();
        $callback = $filters[$this->hook][$this->priority][$this->id]['function'] ?? null;

        if (! $callback) {
            throw new \Exception("Could not find registered callback for hook [{$this->hook}] with ID [{$this->id}] at priority [{$this->priority}].");
        }

        if ($callback instanceof \Closure) {
            throw new \Exception('Closures cannot be queued in Laravel Hooks. Use a class method or static callback instead.');
        }

        $this->manager->markAsQueued($this->hook, $this->id, $connection, $queue);

        return $this;
    }

    /**
     * Validate the result of the listener.
     *
     * @return $this
     */
    public function validate(string|\Closure $rules): self
    {
        $this->manager->validateListener($this->hook, $this->id, $this->priority, $rules);

        return $this;
    }
}
