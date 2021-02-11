<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Transformer\Money;

use Money\Money;
use PHPUnit\Framework\TestCase;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\Transformer\Money\MoneyTransformer;
use stdClass;

class MoneyTransformerTest extends TestCase
{
    private MoneyTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new MoneyTransformer();
    }

    public function provideEmptyValues(): iterable
    {
        yield [null];
        yield [''];
    }

    /**
     * @dataProvider provideEmptyValues
     */
    public function testTransformShouldReturnNullOnNullValues(?string $value): void
    {
        self::assertNull($this->transformer->transform($value));
    }

    public function testTransformShouldThrowOnInvalidArray(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Amount must be numeric');
        $this->transformer->transform(['amount' => 'i am not numeric', 'currency' => 'currency']);
    }

    public function provideNonArrayNorNumericValue(): iterable
    {
        yield [['foobar']];
        yield [new stdClass()];
        yield ['string'];
    }

    /**
     * @dataProvider provideNonArrayNorNumericValue
     */
    public function testTransformShouldThrowOnNonArrayNorNumericValue($value): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Value must be numeric or an array with amount and currency keys set');
        $this->transformer->transform($value);
    }

    public function provideValidTransformValues(): iterable
    {
        yield [['amount' => '50000', 'currency' => 'EUR']];
        yield [Money::EUR('50000')];
    }

    /**
     * @dataProvider provideValidTransformValues
     */
    public function testTransformShouldWork($value): void
    {
        self::assertEquals(Money::EUR('50000'), $this->transformer->transform($value));
    }
}
