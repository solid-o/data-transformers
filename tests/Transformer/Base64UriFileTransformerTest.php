<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Transformer;

use PHPUnit\Framework\TestCase;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\Transformer\Base64UriFileTransformer;
use Solido\DataTransformers\TransformerInterface;
use stdClass;
use Symfony\Component\HttpFoundation\File\File;

class Base64UriFileTransformerTest extends TestCase
{
    protected const TEST_GIF_DATA = 'data:image/gif;base64,R0lGODdhAQABAIAAAP///////ywAAAAAAQABAAACAkQBADs=';
    protected const TEST_TXT_DATA = 'data:text/plain,K%C3%A9vin%20Dunglas%0A';
    protected const TEST_TXT_CONTENT = "Kévin Dunglas\n";

    protected TransformerInterface $transformer;

    protected function setUp(): void
    {
        $this->transformer = new Base64UriFileTransformer();
    }

    public function testTransformShouldReturnNullOnNullValues(): void
    {
        self::assertNull($this->transformer->transform(null));
    }

    public function testTransformShouldNotTouchFileObjects(): void
    {
        $file = new File(__FILE__);

        self::assertSame($file, $this->transformer->transform($file));
    }

    public function provideNonString(): iterable
    {
        yield [0.23];
        yield [47];
        yield [['foobar']];
        yield [new stdClass()];
    }

    /**
     * @dataProvider provideNonString
     */
    public function testTransformShouldThrowOnNonStringValues($value): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->transformer->transform($value);
    }

    public function testTransformShouldThrowOnNonDataUri(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->transformer->transform(self::TEST_TXT_CONTENT);
    }

    public function testTransformShouldTransformPlainData(): void
    {
        $file = $this->transformer->transform(self::TEST_TXT_DATA);

        self::assertInstanceOf(File::class, $file);

        $handle = $file->openFile();
        self::assertEquals(self::TEST_TXT_CONTENT, $handle->fread($handle->getSize()));
    }

    public function testTransformShouldTransformBase64Data(): void
    {
        $file = $this->transformer->transform(self::TEST_GIF_DATA);

        self::assertInstanceOf(File::class, $file);

        $handle = $file->openFile();
        self::assertStringEqualsFile(__DIR__ . '/../Fixtures/test.gif', $handle->fread($handle->getSize()));
        self::assertEquals('image/gif', $file->getMimeType());
    }
}
