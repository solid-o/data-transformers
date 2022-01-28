<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Annotation;

use PHPUnit\Framework\TestCase;
use Solido\DataTransformers\Annotation\Transform;
use TypeError;

class TransformTest extends TestCase
{
    /**
     * @dataProvider provideInvalidValue
     */
    public function testShouldThrowIfValueIsNotAString($value): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Transformer argument must be a string. ' . get_debug_type($value) . ' passed');

        new Transform($value);
    }

    public function provideInvalidValue()
    {
        yield [15];
        yield [[15]];
        yield [.0];
        yield [fopen('php://temp', 'rb')];
    }

    public function testShouldEvaluateTransformerAttributeBeforeValue(): void
    {
        $this->expectNotToPerformAssertions();
        new Transform(['transformer' => 'Transformer', 'value' => 15]);
    }
}
