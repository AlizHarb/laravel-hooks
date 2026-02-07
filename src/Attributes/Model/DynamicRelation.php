<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Attributes\Model;

use Attribute;

#[Attribute(Attribute::TARGET_ALL | Attribute::IS_REPEATABLE)]
class DynamicRelation
{
    public function __construct(
        public ?string $name = null,
        public string $type = 'hasMany',
        public string $related = '',
        public ?string $foreignKey = null,
        public ?string $localKey = null,
        public array $args = []
    ) {}
}
