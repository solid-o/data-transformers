<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Transformer;

use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\TransformerInterface;

use function gettype;
use function in_array;
use function is_bool;
use function is_scalar;
use function Safe\sprintf;
use function strtolower;

/**
 * Transforms string boolean values to a bool.
 */
class BooleanTransformer implements TransformerInterface
{
    public const TRUE_VALUES = ['1', 'true', 'yes', 'on', 'y', 't'];
    public const FALSE_VALUES = ['0', 'false', 'no', 'off', 'n', 'f'];

    /**
     * {@inheritdoc}
     */
    public function transform($value): ?bool
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (! is_scalar($value)) {
            throw new TransformationFailedException(sprintf('Expected a scalar value, %s passed', gettype($value)));
        }

        $value = strtolower((string) $value);
        if (in_array($value, self::TRUE_VALUES, true)) {
            return true;
        }

        if (in_array($value, self::FALSE_VALUES, true)) {
            return false;
        }

        throw new TransformationFailedException('Cannot transform value "' . $value . '"');
    }
}
