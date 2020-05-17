<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Transformer;

use Solido\DataTransformers\TransformerInterface;

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

        $transformed = [];
        foreach ($value as $key => $item) {
            $transformed[$key] = $this->innerTransformer->transform($item);
        }

        return $transformed;
    }
}
