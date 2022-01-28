<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Transformer;

use DateTimeImmutable;
use DateTimeInterface;
use Generator;
use PHPUnit\Framework\TestCase;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\Transformer\DateTransformer;
use stdClass;

use function is_string;
use function iterator_to_array;

class DateTransformerTest extends TestCase
{
    private DateTransformer $transformer;

    public function setUp(): void
    {
        $this->transformer = new DateTransformer();
    }

    public function provideEmptyValues(): iterable
    {
        yield [null];
        yield [''];
    }

    public function provideNonDateTimeInterfaceValues(): Generator
    {
        yield ['2020a07a08'];
        yield ['i am not a phone number'];
        yield [1];
        yield [1.0];
        yield [new stdClass()];
        yield [[]];
    }

    /**
     * @dataProvider provideEmptyValues
     */
    public function testTransformShouldReturnNullOnEmptyValue(?string $value): void
    {
        self::assertNull($this->transformer->transform($value));
    }

    public function provideInvalidTransformValues(): iterable
    {
        foreach ($this->provideNonDateTimeInterfaceValues() as [$value]) {
            yield [$value, is_string($value) ? 'Unexpected date format' : 'Expected a string'];
        }
    }

    /**
     * @dataProvider provideInvalidTransformValues
     */
    public function testTransformShouldThrowOnInvalidValue($value, string $expectedExceptionMessage): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->transformer->transform($value);
    }

    public function testTransformShouldReturnValueOnDateTimeInterfaceValue(): void
    {
        $now = new DateTimeImmutable();

        self::assertEquals($now, $this->transformer->transform($now));
    }

    public function provideDateAsStringValues(): Generator
    {
        $now = new DateTimeImmutable('midnight');

        yield [$now, $now->format('Y-m-d')];
        yield [$now, $now->format('d/m/Y')];

        yield [new DateTimeImmutable('2021-01-28'), '28/01/2021'];
        yield [new DateTimeImmutable('2022-02-28'), '28/02/2022'];
        yield [new DateTimeImmutable('2023-03-02'), '30/02/2023'];
    }

    /**
     * @dataProvider provideDateAsStringValues
     */
    public function testTransformShouldAcceptDateAsString(DateTimeInterface $expected, string $dateAsString): void
    {
        self::assertEquals($expected, $this->transformer->transform($dateAsString));
    }
}
