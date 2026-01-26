<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Attributes\Model;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class DynamicRelation
{
    public function __construct(
        public string $name,
        public string $type,
        public string $related,
        public ?string $foreignKey = null,
        public ?string $localKey = null,
        public array $args = []
    ) {}
}
