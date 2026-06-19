<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Transformer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\Transformer\Base64UriFileTransformer;
use Solido\DataTransformers\TransformerInterface;
use stdClass;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Base64UriFileTransformerTest extends TestCase
{
    protected const TEST_GIF_DATA = 'data:image/gif;filename=test%20image.gif;last-modified=now;base64,R0lGODdhAQABAIAAAP///////ywAAAAAAQABAAACAkQBADs=';
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

    public static function provideNonString(): iterable
    {
        yield [0.23];
        yield [47];
        yield [['foobar']];
        yield [new stdClass()];
    }

    #[DataProvider('provideNonString')]
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
        self::assertInstanceOf(UploadedFile::class, $file);
        self::assertEquals('test image.gif', $file->getClientOriginalName());

        $handle = $file->openFile();
        self::assertStringEqualsFile(__DIR__ . '/../Fixtures/test.gif', $handle->fread($handle->getSize()));
        self::assertEquals('image/gif', $file->getMimeType());
    }

    public function testShouldTransformCanBeOverridden(): void
    {
        $file = new File(__FILE__);
        $transformer = new class extends Base64UriFileTransformer {
            protected function shouldTransform(mixed $value): bool
            {
                return true;
            }
        };

        $this->expectException(TransformationFailedException::class);

        $transformer->transform($file);
    }

    public function testCreateFileCanBeOverridden(): void
    {
        $expected = new stdClass();
        $transformer = new class($expected) extends Base64UriFileTransformer {
            public function __construct(private object $file)
            {
            }

            protected function createFile(string $data, string|null $originalName, string|null $mime): object
            {
                TestCase::assertSame("Kévin Dunglas\n", $data);
                TestCase::assertNull($originalName);
                TestCase::assertSame('text/plain', $mime);

                return $this->file;
            }
        };

        self::assertSame($expected, $transformer->transform(self::TEST_TXT_DATA));
    }
}
