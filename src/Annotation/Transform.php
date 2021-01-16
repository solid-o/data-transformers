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
        if (is_string($transformer)) {
            $data = ['transformer' => $transformer];
        } elseif (is_array($transformer)) {
            $data = $transformer;
        } else {
            throw new TypeError(sprintf('Argument #1 passed to %s must be a string. %s passed', __METHOD__, get_debug_type($transformer)));
        }

        $this->transformer = $data['transformer'] ?? $data['value'] ?? null;
    }
}
