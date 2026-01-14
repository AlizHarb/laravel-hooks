<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class HookFilter
{
    /**
     * Create a new HookFilter instance.
     *
     * @param string $hook The hook name.
     * @param int $priority Priority defaults to 10.
     * @param int $acceptedArgs Number of accepted arguments.
     */
    public function __construct(
        public string $hook,
        public int $priority = 10,
        public int $acceptedArgs = 1
    ) {}
}
