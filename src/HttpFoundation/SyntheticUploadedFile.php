<?php

declare(strict_types=1);

namespace Solido\DataTransformers\HttpFoundation;

use Safe\Exceptions\FilesystemException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use function file_exists;
use function mt_rand;
use function Safe\chmod;
use function Safe\file_put_contents;
use function Safe\rename;
use function Safe\sprintf;
use function Safe\tempnam;
use function Safe\unlink;
use function strip_tags;
use function sys_get_temp_dir;
use function umask;

class SyntheticUploadedFile extends UploadedFile
{
    public function __construct(
        string $contents,
        ?string $originalName = null,
        ?string $mimeType = null,
        ?int $error = null
    ) {
        $tempPath = tempnam(sys_get_temp_dir(), 'synt_uploaded_file');
        file_put_contents($tempPath, $contents);

        parent::__construct($tempPath, $originalName ?? 'up_' . mt_rand(), $mimeType, $error, false);
    }

    /**
     * @infection-ignore-all
     */
    public function __destruct()
    {
        if (! file_exists($this->getPathname())) {
            return;
        }

        try {
            unlink($this->getPathname());
        } catch (FilesystemException $e) {
            // @ignoreException
        }
    }

    public function isValid(): bool
    {
        return true;
    }

    /**
     * @infection-ignore-all
     */
    public function move(string $directory, ?string $name = null): File
    {
        if ($this->isValid()) {
            $target = $this->getTargetFile($directory, $name);

            try {
                rename($this->getPathname(), $target->getPathname());
            } catch (FilesystemException $e) {
                throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $target->getPathname(), strip_tags($e->getMessage())), 0, $e);
            }

            try {
                chmod($target->getPathname(), 0666 & ~umask());
            } catch (FilesystemException $e) {
                // @ignoreException
            }

            return $target;
        }

        throw new FileException($this->getErrorMessage());
    }
}
