<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Fixtures;

use Solido\DataTransformers\Annotation\Transform;

class ProxableClassWithBadTransformer2
{
    /** @Transform(transformer="stdClass") */
    public string $foo;
}
