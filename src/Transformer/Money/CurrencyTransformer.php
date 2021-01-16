<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Transformer\Money;

use Money\Currency;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\TransformerInterface;

use function is_string;

/**
 * This transformer requires the moneyphp/money library.
 */
class CurrencyTransformer implements TransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value): ?Currency
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Currency) {
            return $value;
        }

        if (! is_string($value)) {
            throw new TransformationFailedException('Expected a string');
        }

        return new Currency($value);
    }
}
