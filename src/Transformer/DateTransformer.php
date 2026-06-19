<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Transformer;

use DateTimeImmutable;
use DateTimeInterface;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\TransformerInterface;

use function is_string;
use function preg_match;

class DateTransformer implements TransformerInterface
{
    /**
     * {@inheritDoc}
     */
    public function transform($value): DateTimeInterface|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        if (! is_string($value)) {
            throw new TransformationFailedException('Expected a string');
        }

        $dateTime = new DateTimeImmutable('midnight');
        $matches = [];
        if (preg_match('#^(\d{2})/(\d{2})/(\d{4,})$#', $value, $matches) === 1) {
            return $dateTime->setDate((int) $matches[3], (int) $matches[2], (int) $matches[1]);
        }

        $matches = [];
        if (preg_match('/^(\d{4,})-(\d{2})-(\d{2})$/', $value, $matches) === 1) {
            return $dateTime->setDate((int) $matches[1], (int) $matches[2], (int) $matches[3]);
        }

        throw new TransformationFailedException('Unexpected date format');
    }
}
