<?php declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Transformer;

use PHPUnit\Framework\TestCase;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\Transformer\IntegerTransformer;

class IntegerTransformerTest extends TestCase
{
    private IntegerTransformer $transformer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->transformer = new IntegerTransformer();
    }

    public function provideEmptyValues(): iterable
    {
        yield [null];
        yield [''];
    }

    /**
     * @dataProvider provideEmptyValues
     */
    public function testTransformShouldReturnNullOnEmptyValue(?string $value): void
    {
        self::assertNull($this->transformer->transform($value));
    }

    public function testTransformerShouldReturnIntIfValueIsInt(): void
    {
        $value = 42;

        self::assertEquals($value, $this->transformer->transform($value));
    }

    public function provideNonNumericValues(): iterable
    {
        yield ['i am not a phone number'];
        yield [new \stdClass()];
        yield [[]];
    }

    /**
     * @dataProvider provideNonNumericValues
     */
    public function testTransformShouldThrowOnNonNumericStrings($value): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Cannot transform a non-numeric string value to an integer');
        $this->transformer->transform($value);
    }

    public function testTransformerShouldTransformNumericStrings(): void
    {
        self::assertEquals(12345, $this->transformer->transform('12345'));
    }
}
