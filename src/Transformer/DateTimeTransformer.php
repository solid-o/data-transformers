<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Transformer;

use DateTimeInterface;
use DateTimeZone;
use Safe\DateTime;
use Safe\DateTimeImmutable;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\TransformerInterface;
use Throwable;

use function checkdate;
use function is_string;
use function Safe\preg_match;
use function Safe\sprintf;

class DateTimeTransformer implements TransformerInterface
{
    private ?string $outputTimezone;
    private bool $asImmutable;

    public function __construct(?string $outputTimezone = null, bool $asImmutable = true)
    {
        $this->outputTimezone = $outputTimezone;
        $this->asImmutable = $asImmutable;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value): ?DateTimeInterface
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            if ($value instanceof \DateTime && $this->asImmutable) {
                return \DateTimeImmutable::createFromMutable($value);
            }

            if ($value instanceof \DateTimeImmutable && ! $this->asImmutable) {
                return \DateTime::createFromImmutable($value);
            }

            return $value;
        }

        if (! is_string($value)) {
            throw new TransformationFailedException('Expected a string.');
        }

        if ($value === '') {
            return null;
        }

        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})T\d{2}:\d{2}(?::\d{2})?(?:\.\d+)?(?:Z|(?:(?:\+|-)\d{2}:?\d{2}))$/', $value, $matches)) {
            throw new TransformationFailedException(sprintf('The date "%s" is not a valid date.', $value));
        }

        try {
            $dateTime = $this->asImmutable ? new DateTimeImmutable($value) : new DateTime($value);
        } catch (Throwable $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }

        if ($this->outputTimezone !== null && $this->outputTimezone !== $dateTime->getTimezone()->getName()) {
            $dateTime = $dateTime->setTimezone(new DateTimeZone($this->outputTimezone));
        }

        if (! checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1])) {
            throw new TransformationFailedException(sprintf('The date "%s-%s-%s" is not a valid date.', $matches[1], $matches[2], $matches[3]));
        }

        return $dateTime;
    }
}
