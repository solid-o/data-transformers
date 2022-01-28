<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Transformer;

use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\TransformerInterface;

use function is_iterable;

class MappingTransformer implements TransformerInterface
{
    private TransformerInterface $innerTransformer;

    public function __construct(TransformerInterface $innerTransformer)
    {
        $this->innerTransformer = $innerTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value): ?array
    {
        if (empty($value)) {
            return [];
        }

        if (! is_iterable($value)) {
            throw new TransformationFailedException('Value is not iterable');
        }

        $transformed = [];
        foreach ($value as $key => $item) {
            $transformed[$key] = $this->innerTransformer->transform($item);
        }

        return $transformed;
    }
}
