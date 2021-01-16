<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Transformer;

use Solido\Common\Exception\ResourceNotFoundException;
use Solido\Common\Urn\Urn;
use Solido\Common\Urn\UrnConverterInterface;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\TransformerInterface;

use function get_class;
use function gettype;
use function is_object;
use function is_string;

class UrnToItemTransformer implements TransformerInterface
{
    private UrnConverterInterface $urnConverter;

    public function __construct(UrnConverterInterface $urnConverter)
    {
        $this->urnConverter = $urnConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value, ?string $acceptable = null): ?object
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_object($value) && ! $value instanceof Urn) {
            return $value;
        }

        if (! is_string($value) && ! $value instanceof Urn) {
            throw new TransformationFailedException('Expected a string. Got ' . (is_object($value) ? get_class($value) : gettype($value)));
        }

        $urn = new Urn($value);

        try {
            return $this->urnConverter->getItemFromUrn($urn, $acceptable);
        } catch (ResourceNotFoundException $e) {
            throw new TransformationFailedException('Could not find the desired object with ID ' . $urn->id, 0, $e);
        }
    }
}
