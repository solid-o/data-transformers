<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Transformer;

class NullableMappingTransformer extends MappingTransformer
{
    /**
     * {@inheritdoc}
     */
    public function transform($value): ?array
    {
        if ($value === null) {
            return null;
        }

        return parent::transform($value);
    }
}
