<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Transformer;

use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\TransformerInterface;

use function is_float;
use function is_int;
use function is_numeric;
use function is_string;

class IntegerTransformer implements TransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (! is_float($value) && (! is_string($value) || ! is_numeric($value))) {
            throw new TransformationFailedException('Cannot transform a non-numeric string value to an integer');
        }

        return (int) $value;
    }
}
