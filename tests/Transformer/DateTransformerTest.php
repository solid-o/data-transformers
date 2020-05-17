<?php declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Transformer;

use PHPUnit\Framework\TestCase;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\Transformer\DateTransformer;

class DateTransformerTest extends TestCase
{
    private DateTransformer $transformer;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->transformer = new DateTransformer();
    }

    public function provideEmptyValues(): iterable
    {
        yield [null];
        yield [''];
    }

    public function provideNonDateTimeInterfaceValues(): \Generator
    {
        yield ['i am not a phone number'];
        yield [1];
        yield [1.0];
        yield [new \stdClass()];
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
        foreach (\iterator_to_array($this->provideNonDateTimeInterfaceValues()) as $value) {
            yield [$value, \is_string($value) ? 'Unexpected date format' : 'Expected a string'];
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
        $now = new \DateTimeImmutable();

        self::assertEquals($now, $this->transformer->transform($now));
    }

    public function provideDateAsStringValues(): \Generator
    {
        $now = new \DateTimeImmutable('midnight');
        yield [$now, $now->format('Y-m-d')];
        yield [$now, $now->format('d/m/Y')];
    }

    /**
     * @dataProvider provideDateAsStringValues
     */
    public function testTransformShouldAcceptDateAsString(\DateTimeInterface $expected, string $dateAsString): void
    {
        self::assertEquals($expected, $this->transformer->transform($dateAsString));
    }
}
