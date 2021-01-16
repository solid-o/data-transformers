<?php declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Fixtures;

use Solido\DataTransformers\Annotation\Transform;
use Solido\DataTransformers\Transformer\BooleanTransformer;
use Solido\DataTransformers\Transformer\DateTimeTransformer;

class ProxableClassWithAttributes
{
    #[Transform(BooleanTransformer::class)]
    public bool $boolean;

    #[Transform(DateTimeTransformer::class)]
    public \DateTimeInterface $dateTime;
}