<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Fixtures;

use Solido\DataTransformers\Annotation\Transform;
use Solido\DataTransformers\Transformer\BooleanTransformer;

class ProxableClassWithPrivateMethod
{
    /**
     * @Transform(BooleanTransformer::class)
     */
    private function setBool(bool $newBool): void
    {
    }
}
