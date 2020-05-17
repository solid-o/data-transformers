<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Transformer;

use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\TransformerInterface;
use Solido\Pagination\Exception\InvalidTokenException;
use Solido\Pagination\PageToken;

class PageTokenTransformer implements TransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value): ?PageToken
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof PageToken) {
            return $value;
        }

        try {
            return PageToken::parse($value);
        } catch (InvalidTokenException $e) {
            throw new TransformationFailedException('Invalid token provided', 0, $e);
        }
    }
}
