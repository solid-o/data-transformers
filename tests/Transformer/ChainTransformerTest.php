<?php declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Transformer;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Solido\DataTransformers\Transformer\ChainTransformer;
use Solido\DataTransformers\TransformerInterface;

class ChainTransformerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var TransformerInterface|ObjectProphecy
     */
    private ObjectProphecy $innerTransformer1;

    /**
     * @var TransformerInterface|ObjectProphecy
     */
    private ObjectProphecy $innerTransformer2;
    private ChainTransformer $transformer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->innerTransformer1 = $this->prophesize(TransformerInterface::class);
        $this->innerTransformer2 = $this->prophesize(TransformerInterface::class);
        $this->transformer = new ChainTransformer($this->innerTransformer1->reveal(), $this->innerTransformer2->reveal());
    }

    public function testTransformShouldCallInnerTransformersAndReturnLastTransformedValue(): void
    {
        $value = 'the value';
        $transformedValue1 = 'the first transformation';
        $transformedValue2 = 'the last transformation';

        $this->innerTransformer1->transform($value)
            ->shouldBeCalled()
            ->willReturn($transformedValue1)
        ;

        $this->innerTransformer2->transform($transformedValue1)
            ->shouldBeCalled()
            ->willReturn($transformedValue2)
        ;

        self::assertEquals($transformedValue2, $this->transformer->transform($value));
    }
}
