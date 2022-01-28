<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse

namespace Solido\DataTransformers\Annotation;

use Attribute;
use TypeError;

use function get_debug_type;
use function is_array;
use function is_string;
use function Safe\sprintf;

/**
 * @Annotation()
 */
#[Attribute]
class Transform
{
    /** @Required() */
    public string $transformer;

    /**
     * @param string|array<string, mixed> $transformer
     */
    public function __construct($transformer)
    {
        $data = ! is_array($transformer) ? ['transformer' => $transformer] : $transformer;
        $value = $data['transformer'] ?? $data['value'] ?? null;
        if (! is_string($value)) {
            throw new TypeError(sprintf('Transformer argument must be a string. %s passed', get_debug_type($transformer)));
        }

        $this->transformer = $value;
    }
}
