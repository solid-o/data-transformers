<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Fixtures;

use DateTimeInterface;
use Solido\DataTransformers\Annotation\Transform;
use Solido\DataTransformers\Transformer\BooleanTransformer;
use Solido\DataTransformers\Transformer\DateTimeTransformer;

class ProxableClass
{
    public bool $newBool;

    #[Transform(BooleanTransformer::class)]
    public bool $boolean;

    #[Transform(DateTimeTransformer::class)]
    public DateTimeInterface $dateTime;

    public function method1(): void
    {
    }

    public function method2(): void
    {
    }

    #[Transform(BooleanTransformer::class)]
    public function setNewBool(bool $newBool): void
    {
        $this->newBool = $newBool;
    }
}
