<?php declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Fixtures;

use Solido\DataTransformers\Annotation\Transform;

class ProxableClassWithNonExistentTransformer
{
    /**
     * @Transform(transformer="NonExistent")
     */
    public string $foo;
}
