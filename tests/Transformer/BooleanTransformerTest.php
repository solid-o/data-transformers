<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Transformer;

use PHPUnit\Framework\TestCase;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\Transformer\BooleanTransformer;
use stdClass;

class BooleanTransformerTest extends TestCase
{
    private BooleanTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new BooleanTransformer();
    }

    public function testTransformShouldReturnNullOnNull(): void
    {
        self::assertNull($this->transformer->transform(null));
    }

    public function provideBooleanValues(): iterable
    {
        yield [true];
        yield [false];
    }

    /**
     * @dataProvider provideBooleanValues
     */
    public function testTransformShouldReturnBoolOnBooleans(bool $value): void
    {
        self::assertEquals($value, $this->transformer->transform($value));
    }

    public function testTransformShouldThrowOnObjects(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a scalar value, object passed');
        $this->transformer->transform(new stdClass());
    }

    public function testTransformShouldThrowOnArrays(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a scalar value, array passed');
        $this->transformer->transform([]);
    }

    public function provideFalseValues(): iterable
    {
        foreach (BooleanTransformer::FALSE_VALUES as $falseValue) {
            yield [$falseValue];
        }
    }

    /**
     * @dataProvider provideFalseValues
     */
    public function testTransformShouldReturnFalseOnFalseValues(string $value): void
    {
        self::assertFalse($this->transformer->transform($value));
    }

    public function provideTrueValues(): iterable
    {
        foreach (BooleanTransformer::TRUE_VALUES as $trueValue) {
            yield [$trueValue];
        }
    }

    /**
     * @dataProvider provideTrueValues
     */
    public function testTransformShouldReturnTrueOnTrueValues(string $value): void
    {
        self::assertTrue($this->transformer->transform($value));
    }

    public function testTransformShouldThrowOnInvalidStrings(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Cannot transform value "i_am_not_a_false_value_nor_true_value"');
        $this->transformer->transform('i_am_not_a_false_value_nor_true_value');
    }
}
