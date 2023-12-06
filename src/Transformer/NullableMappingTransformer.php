<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Transformer;

class NullableMappingTransformer extends MappingTransformer
{
    /**
     * {@inheritDoc}
     */
    public function transform($value): array|null
    {
        if ($value === null) {
            return null;
        }

        return parent::transform($value);
    }
}
