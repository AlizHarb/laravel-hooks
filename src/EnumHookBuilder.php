<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks;

class EnumHookBuilder
{
    public function __construct(
        protected HookManager $manager,
        protected string $enumClass
    ) {}

    /**
     * Add a virtual case to the enum.
     *
     * @param string $name The name of the case (e.g. 'Archived')
     * @param mixed $value The value of the case (e.g. 'archived')
     * @param array $extra Optional extra metadata
     * @return $this
     */
    public function addCase(string $name, mixed $value, array $extra = []): self
    {
        $this->manager->registerEnumCase($this->enumClass, $name, $value, $extra);

        return $this;
    }
}
