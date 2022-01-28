<?php declare(strict_types=1);

namespace Solido\DataTransformers\Tests\HttpFoundation;

use Solido\DataTransformers\HttpFoundation\SyntheticUploadedFile;
use PHPUnit\Framework\TestCase;

class SyntheticUploadedFileTest extends TestCase
{
    private SyntheticUploadedFile $file;

    protected function setUp(): void
    {
        $this->file = new SyntheticUploadedFile('foobar');
    }

    public function testIsValid(): void
    {
        self::assertTrue($this->file->isValid());
    }

    public function testShouldGenerateOriginalName(): void
    {
        self::assertMatchesRegularExpression('/up_.+/', $this->file->getClientOriginalName());
    }
}
