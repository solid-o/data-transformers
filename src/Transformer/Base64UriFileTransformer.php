<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Transformer;

use Safe\Exceptions\UrlException;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\HttpFoundation\SyntheticUploadedFile;
use Solido\DataTransformers\TransformerInterface;
use Symfony\Component\HttpFoundation\File\File;

use function array_column;
use function array_filter;
use function array_map;
use function explode;
use function is_string;
use function Safe\base64_decode;
use function Safe\preg_match;
use function urldecode;

/**
 * Transforms a base64-encoded file to an object instance of UploadedFile.
 */
class Base64UriFileTransformer implements TransformerInterface
{
    private const DATA_URI_PATTERN = '/^data:([a-z0-9][a-z0-9\!\#\$\&\-\^\_\+\.]{0,126}\/[a-z0-9][a-z0-9\!\#\$\&\-\^\_\+\.]{0,126})((?:;[a-z0-9\-]+=[^\/\\\?\*:\|\"<>;=]+)*?)?(;base64)?,([a-z0-9\!\$\&\\\'\,\(\)\*\+\,\;\=\-\.\_\~\:\@\/\?\%\s]*\s*)$/i';

    /**
     * {@inheritdoc}
     */
    public function transform($value): ?object
    {
        if ($value === null) {
            return null;
        }

        if (! $this->shouldTransform($value)) {
            return $value;
        }

        if (! is_string($value)) {
            throw new TransformationFailedException('Cannot transform a non-string value to an instance of UploadedFile.');
        }

        if (! preg_match(self::DATA_URI_PATTERN, $value, $matches)) {
            throw new TransformationFailedException('Invalid data: URI provided');
        }

        [, $mime, $attributes, $base64, $data] = $matches;

        if (! empty($attributes)) {
            $attributes = array_filter(array_map(
                static fn ($value) => array_map('urldecode', explode('=', $value)),
                explode(';', $attributes)
            ));

            $attributes = array_column($attributes, 1, 0);
        } else {
            $attributes = [];
        }

        if (! empty($base64)) {
            try {
                $data = base64_decode($data, true);
            } catch (UrlException $e) {
                throw new TransformationFailedException('Cannot decode base64 to string.', 0, $e);
            }
        } else {
            $data = urldecode($data);
        }

        return $this->createFile($data, $attributes['filename'] ?? null, $mime);
    }

    /**
     * Whether the passed values should be transformed.
     *
     * @param mixed $value
     */
    protected function shouldTransform($value): bool
    {
        return ! $value instanceof File;
    }

    protected function createFile(string $data, ?string $originalName, ?string $mime): object
    {
        return new SyntheticUploadedFile($data, $originalName, $mime);
    }
}
