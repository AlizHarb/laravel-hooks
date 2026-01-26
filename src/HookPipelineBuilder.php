<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks;

/**
 * Builds a pipeline of filters for a specific hook.
 */
class HookPipelineBuilder
{
    /**
     * Create a new HookPipelineBuilder instance.
     *
     * @param HookManager $manager
     * @param string $hook
     */
    public function __construct(
        protected HookManager $manager,
        protected string $hook
    ) {
    }

    /**
     * Set the pipes (filters) for the hook.
     *
     * @param array $pipes
     * @return self
     */
    public function through(array $pipes): self
    {
        foreach ($pipes as $pipe) {
            $this->manager->addFilter($this->hook, function ($content, ...$args) use ($pipe) {
                if (is_string($pipe)) {
                    $instance = app($pipe);
                    if (method_exists($instance, 'handle')) {
                        return $instance->handle($content, fn ($c) => $c, ...$args);
                    }
                }

                if (is_callable($pipe)) {
                    return $pipe($content, fn ($c) => $c, ...$args);
                }

                return $content;
            });
        }

        return $this;
    }
}
