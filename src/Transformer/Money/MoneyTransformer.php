<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Transformer\Money;

use Money\Currency;
use Money\Money;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\TransformerInterface;

use function is_array;
use function is_numeric;

/**
 * This transformer requires the moneyphp/money library.
 */
class MoneyTransformer implements TransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value): ?Money
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Money) {
            return $value;
        }

        if (is_array($value) && isset($value['amount'], $value['currency'])) {
            if (! is_numeric($value['amount'])) {
                throw new TransformationFailedException('Amount must be numeric');
            }

            return new Money((string) $value['amount'], new Currency($value['currency']));
        }

        if (! is_numeric($value)) {
            throw new TransformationFailedException('Value must be numeric or an array with amount and currency keys set');
        }

        return Money::EUR((string) $value);
    }
}
