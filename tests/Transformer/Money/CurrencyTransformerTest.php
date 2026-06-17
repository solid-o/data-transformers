<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Transformer\Money;

use Money\Currency;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public static function provideEmptyValues(): iterable
    {
        yield [null];
        yield [''];
    }

    public static function provideNonStringValues(): iterable
    {
        yield [0.23];
        yield [47];
        yield [47];
        yield [['foobar']];
        yield [new stdClass()];
    }

    #[DataProvider('provideEmptyValues')]
    public function testTransformShouldReturnNullOnNullValues(?string $value): void
    {
        self::assertNull($this->transformer->transform($value));
    }

    #[DataProvider('provideNonStringValues')]
    public function testTransformShouldThrowOnNonValidValues($value): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a string');
        $this->transformer->transform($value);
    }

    public static function provideValidTransformValues(): iterable
    {
        yield ['EUR'];
        yield [new Currency('EUR')];
    }

    #[DataProvider('provideValidTransformValues')]
    public function testTransformShouldWork($value): void
    {
        self::assertEquals(new Currency('EUR'), $this->transformer->transform($value));
    }
}
