<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Transformer;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Solido\DataTransformers\Transformer\NullableMappingTransformer;
use Solido\DataTransformers\TransformerInterface;
use stdClass;

class NullableMappingTransformerTest extends TestCase
{
    use ProphecyTrait;

    /** @var TransformerInterface|ObjectProphecy */
    private ObjectProphecy $innerTransformer;
    private NullableMappingTransformer $transformer;

    protected function setUp(): void
    {
        $this->innerTransformer = $this->prophesize(TransformerInterface::class);
        $this->transformer = new NullableMappingTransformer($this->innerTransformer->reveal());
    }

    public function testTransformShouldReturnNullOnNull(): void
    {
        $this->innerTransformer->transform(Argument::any())->shouldNotBeCalled();

        self::assertNull($this->transformer->transform(null));
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
}
