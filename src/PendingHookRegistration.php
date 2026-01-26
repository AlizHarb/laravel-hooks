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
    ) {
    }

    /**
     * Only register the hook if the condition is met.
     *
     * @param bool|Closure $condition
     * @return self
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
     *
     * @param string|array $environments
     * @return self
     */
    public function onlyInEnvironment(string|array $environments): self
    {
        return $this->when(app()->environment($environments));
    }
}
