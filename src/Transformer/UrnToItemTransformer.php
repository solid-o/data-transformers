<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Transformer;

use Solido\Common\Exception\InvalidArgumentException;
use Solido\Common\Exception\ResourceNotFoundException;
use Solido\Common\Urn\Urn;
use Solido\Common\Urn\UrnConverterInterface;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\TransformerInterface;

use function get_debug_type;
use function is_object;
use function is_string;

class UrnToItemTransformer implements TransformerInterface
{
    public function __construct(private UrnConverterInterface $urnConverter)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @phpstan-param class-string<object>|null $acceptable
     */
    public function transform($value, string|null $acceptable = null): object|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_object($value)) {
            if (! $value instanceof Urn) {
                return $value;
            }
        } elseif (! is_string($value)) {
            throw new TransformationFailedException('Expected a string. Got ' . get_debug_type($value));
        }

        try {
            $urn = new Urn($value);
        } catch (InvalidArgumentException $e) {
            throw new TransformationFailedException('Invalid URN', 0, $e);
        }

        try {
            return $this->urnConverter->getItemFromUrn($urn, $acceptable);
        } catch (ResourceNotFoundException $e) {
            throw new TransformationFailedException('Could not find the desired object with ID ' . $urn->id, 0, $e);
        }
    }
}
