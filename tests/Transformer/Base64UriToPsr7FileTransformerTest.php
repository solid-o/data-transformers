<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Transformer;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\UploadedFile;
use Psr\Http\Message\UploadedFileInterface;
use Solido\DataTransformers\Transformer\Base64UriToPsr7FileTransformer;

class Base64UriToPsr7FileTransformerTest extends Base64UriFileTransformerTest
{
    protected function setUp(): void
    {
        $factory = new Psr17Factory();
        $this->transformer = new Base64UriToPsr7FileTransformer($factory, $factory);
    }

    public function testTransformShouldNotTouchFileObjects(): void
    {
        $file = new UploadedFile(__FILE__, filesize(__FILE__), UPLOAD_ERR_OK);

        self::assertSame($file, $this->transformer->transform($file));
    }

    public function testTransformShouldTransformPlainData(): void
    {
        $file = $this->transformer->transform(self::TEST_TXT_DATA);

        self::assertInstanceOf(UploadedFileInterface::class, $file);

        $stream = $file->getStream();
        self::assertEquals(self::TEST_TXT_CONTENT, (string) $stream);
    }

    public function testTransformShouldTransformBase64Data(): void
    {
        $file = $this->transformer->transform(self::TEST_GIF_DATA);

        self::assertInstanceOf(UploadedFileInterface::class, $file);

        $stream = $file->getStream();
        self::assertStringEqualsFile(__DIR__ . '/../Fixtures/test.gif', (string) $stream);
        self::assertEquals('image/gif', $file->getClientMediaType());
    }
}
