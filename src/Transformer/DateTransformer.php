<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Transformer;

use DateTimeInterface;
use Safe\DateTimeImmutable;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\TransformerInterface;
use function is_string;
use function Safe\preg_match;

class DateTransformer implements TransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value): ?DateTimeInterface
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
        if (preg_match('/(\d{2})\/(\d{2})\/(\d{4,})/', $value, $matches)) {
            return $dateTime->setDate((int) $matches[3], (int) $matches[2], (int) $matches[1]);
        }

        if (preg_match('/(\d{4,})-(\d{2})-(\d{2})/', $value, $matches)) {
            return $dateTime->setDate((int) $matches[1], (int) $matches[2], (int) $matches[3]);
        }

        throw new TransformationFailedException('Unexpected date format');
    }
}
