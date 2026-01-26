<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Attributes\Model;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class DynamicAccessor
{
    public function __construct(
        public ?string $name = null
    ) {}
}
