<?php

declare(strict_types=1);

namespace Solido\DataTransformers;

use Solido\DataTransformers\Exception\TransformationFailedException;

interface TransformerInterface
{
    /**
     * Transforms a value to another representation.
     *
     * This method must be able to deal with empty values. Usually this will
     * be an empty string, but depending on your implementation other empty
     * values are possible as well (such as NULL). The reasoning behind
     * this is that value transformers must be chainable. If the
     * transform() method of the first value transformer outputs an
     * empty string, the second value transformer must be able to process that
     * value.
     *
     * By convention, transform() should return NULL if an empty string is passed.
     *
     * @param mixed $value The value in the transformed representation
     *
     * @return mixed The transformed value
     *
     * @throws TransformationFailedException when the transformation fails
     */
    public function transform($value);
}
