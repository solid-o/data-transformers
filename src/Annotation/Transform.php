<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Annotation;

/**
 * @Annotation()
 */
class Transform
{
    /** @Required() */
    public string $transformer;
}
