<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Transformer;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Solido\Common\Exception\ResourceNotFoundException;
use Solido\Common\Urn\Urn;
use Solido\Common\Urn\UrnConverterInterface;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\Transformer\UrnToItemTransformer;
use stdClass;

use function fopen;

class UrnToItemTransformerTest extends TestCase
{
    use ProphecyTrait;

    /** @var UrnConverterInterface|ObjectProphecy */
    private ObjectProphecy $urnConverter;

    private UrnToItemTransformer $transformer;

    protected function setUp(): void
    {
        $this->urnConverter = $this->prophesize(UrnConverterInterface::class);
        $this->transformer = new UrnToItemTransformer($this->urnConverter->reveal());
    }

    public function testShouldTransformToNull(): void
    {
        self::assertNull($this->transformer->transform(''));
        self::assertNull($this->transformer->transform(null));
    }

    public function testShouldNotTransformIfObjectIsPassed(): void
    {
        $obj = new stdClass();

        self::assertSame($obj, $this->transformer->transform($obj));
    }

    public function provideNotUrn(): iterable
    {
        yield [true];
        yield [42];
        yield [[]];
        yield [fopen('php://temp', 'rb')];
    }

    /**
     * @dataProvider provideNotUrn
     */
    public function testShouldThrowIfNotUrnIsPassed($value): void
    {
        $this->expectException(TransformationFailedException::class);

        $this->transformer->transform($value);
    }

    public function testShouldTransformToObject(): void
    {
        $obj = new stdClass();
        $this->urnConverter->getItemFromUrn(new Urn('test'), null)->willReturn($obj);

        self::assertSame($obj, $this->transformer->transform('test'));
        self::assertSame($obj, $this->transformer->transform(new Urn('test')));
    }

    public function testShouldFailIfNotFound(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->urnConverter->getItemFromUrn(new Urn('test'), null)
            ->willThrow(ResourceNotFoundException::class);

        $this->transformer->transform('test');
    }
}
