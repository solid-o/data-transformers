<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Transformer;

use PHPUnit\Framework\TestCase;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\Transformer\PageTokenTransformer;
use Solido\Pagination\PageToken;
use stdClass;

class PageTokenTransformerTest extends TestCase
{
    private PageTokenTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new PageTokenTransformer();
    }

    public function testTransformShouldReturnNullOnNull(): void
    {
        self::assertNull($this->transformer->transform(null));
    }

    public function testTransformShouldReturnPageToken(): void
    {
        $value = PageToken::parse('bfdkg0_1_7gqxdp');
        self::assertEquals($value, $this->transformer->transform($value));

        $value = PageToken::parse('bfdkg0_1_7gqxdp');
        self::assertEquals($value, $this->transformer->transform('bfdkg0_1_7gqxdp'));
    }

    public function testTransformShouldThrowOnObjects(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a string value, object passed');
        $this->transformer->transform(new stdClass());
    }

    public function testTransformShouldThrowOnArrays(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a string value, array passed');
        $this->transformer->transform([]);
    }

    public function testTransformShouldThrowOnInvalidStrings(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Invalid token provided');
        $this->transformer->transform('i_am_not_a_false_value_nor_true_value');
    }
}
