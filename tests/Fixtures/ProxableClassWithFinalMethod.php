<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Fixtures;

use Solido\DataTransformers\Annotation\Transform;
use Solido\DataTransformers\Transformer\BooleanTransformer;

class ProxableClassWithFinalMethod
{
    /**
     * @Transform(BooleanTransformer::class)
     */
    final public function setBool(bool $newBool): void
    {
    }
}
