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
    public function transform($iso8601): ?DateTimeInterface
    {
        if ($iso8601 === null || $iso8601 instanceof DateTimeInterface) {
            return $iso8601;
        }

        if (! is_string($iso8601)) {
            throw new TransformationFailedException('Expected a string.');
        }

        if ($iso8601 === '') {
            return null;
        }

        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})T\d{2}:\d{2}(?::\d{2})?(?:\.\d+)?(?:Z|(?:(?:\+|-)\d{2}:?\d{2}))$/', $iso8601, $matches)) {
            throw new TransformationFailedException(sprintf('The date "%s" is not a valid date.', $iso8601));
        }

        try {
            $dateTime = $this->asImmutable ? new DateTimeImmutable($iso8601) : new DateTime($iso8601);
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
