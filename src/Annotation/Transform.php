<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Annotation;

use Attribute;

#[Attribute]
class Transform
{
    public function __construct(
        public readonly string $transformer,
    ) {
    }
}
