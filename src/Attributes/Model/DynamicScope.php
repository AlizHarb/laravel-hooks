<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Attributes\Model;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class DynamicScope
{
    public function __construct(
        public string $name,
        public ?string $callback = null
    ) {}
}
