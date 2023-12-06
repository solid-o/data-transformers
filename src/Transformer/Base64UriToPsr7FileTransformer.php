<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Transformer;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;

use function strlen;

use const UPLOAD_ERR_OK;

/**
 * Transforms a base64-encoded file to an object instance of UploadedFileInterface.
 */
class Base64UriToPsr7FileTransformer extends Base64UriFileTransformer
{
    public function __construct(private UploadedFileFactoryInterface $uploadedFileFactory, private StreamFactoryInterface $streamFactory)
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function shouldTransform(mixed $value): bool
    {
        return ! $value instanceof UploadedFileInterface;
    }

    protected function createFile(string $data, string|null $originalName, string|null $mime): object
    {
        $stream = $this->streamFactory->createStream($data);

        return $this->uploadedFileFactory->createUploadedFile($stream, strlen($data), UPLOAD_ERR_OK, $originalName, $mime);
    }
}
