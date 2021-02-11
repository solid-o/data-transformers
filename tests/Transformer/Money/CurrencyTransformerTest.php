<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Transformer\Money;

use Money\Currency;
use PHPUnit\Framework\TestCase;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\Transformer\Money\CurrencyTransformer;
use stdClass;

class CurrencyTransformerTest extends TestCase
{
    private CurrencyTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new CurrencyTransformer();
    }

    public function provideEmptyValues(): iterable
    {
        yield [null];
        yield [''];
    }

    public function provideNonStringValues(): iterable
    {
        yield [0.23];
        yield [47];
        yield [47];
        yield [['foobar']];
        yield [new stdClass()];
    }

    /**
     * @dataProvider provideEmptyValues
     */
    public function testTransformShouldReturnNullOnNullValues(?string $value): void
    {
        self::assertNull($this->transformer->transform($value));
    }

    /**
     * @dataProvider provideNonStringValues
     */
    public function testTransformShouldThrowOnNonValidValues($value): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a string');
        $this->transformer->transform($value);
    }

    public function provideValidTransformValues(): iterable
    {
        yield ['EUR'];
        yield [new Currency('EUR')];
    }

    /**
     * @dataProvider provideValidTransformValues
     */
    public function testTransformShouldWork($value): void
    {
        self::assertEquals(new Currency('EUR'), $this->transformer->transform($value));
    }
}
