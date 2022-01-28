<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Transformer;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\Transformer\MappingTransformer;
use Solido\DataTransformers\TransformerInterface;
use stdClass;

use function fopen;

class MappingTransformerTest extends TestCase
{
    use ProphecyTrait;

    /** @var TransformerInterface|ObjectProphecy */
    private ObjectProphecy $innerTransformer;
    private MappingTransformer $transformer;

    protected function setUp(): void
    {
        $this->innerTransformer = $this->prophesize(TransformerInterface::class);
        $this->transformer = new MappingTransformer($this->innerTransformer->reveal());
    }

    public function provideEmptyValues(): iterable
    {
        yield [null];
        yield [''];
        yield [[]];
    }

    /**
     * @dataProvider provideEmptyValues
     */
    public function testTransformShouldReturnEmptyArrayOnEmptyValues($value): void
    {
        $this->innerTransformer->transform(Argument::any())->shouldNotBeCalled();

        self::assertEquals([], $this->transformer->transform($value));
    }

    public function provideElements(): iterable
    {
        yield [['we', 'are', 'the', 'elements', 123, [], new stdClass()]];
    }

    /**
     * @dataProvider provideElements
     */
    public function testTransformShouldCallInnerTransformForEachElement(array $elements): void
    {
        foreach ($elements as $element) {
            $this->innerTransformer->transform($element)->shouldBeCalled();
        }

        $this->transformer->transform($elements);
    }

    public function testTransformShouldThrowIfValueIsNotIterable(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Value is not iterable');

        $this->transformer->transform(fopen('php://temp', 'rb+'));
    }
}
