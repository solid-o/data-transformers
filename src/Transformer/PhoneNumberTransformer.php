<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Transformer;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\TransformerInterface;

use function is_string;

class PhoneNumberTransformer implements TransformerInterface
{
    /**
     * {@inheritDoc}
     */
    public function transform($value): PhoneNumber|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof PhoneNumber) {
            return $value;
        }

        if (! is_string($value)) {
            throw new TransformationFailedException('Expected a string.');
        }

        $util = PhoneNumberUtil::getInstance();
        try {
            return $util->parse($value);
        } catch (NumberParseException $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
