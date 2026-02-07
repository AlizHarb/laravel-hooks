<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Attributes\Model;

use Attribute;

#[Attribute(Attribute::TARGET_ALL | Attribute::IS_REPEATABLE)]
class DynamicScope
{
    public function __construct(
        public ?string $name = null,
        public ?string $callback = null
    ) {}
}
