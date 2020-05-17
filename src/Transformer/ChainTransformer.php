<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Transformer;

use Solido\DataTransformers\TransformerInterface;

class ChainTransformer implements TransformerInterface
{
    /** @var TransformerInterface[] */
    private array $transformers;

    public function __construct(TransformerInterface ...$transformers)
    {
        $this->transformers = $transformers;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        foreach ($this->transformers as $transformer) {
            $value = $transformer->transform($value);
        }

        return $value;
    }
}
