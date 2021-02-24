<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Transformer;

use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\Transformer\DateTimeTransformer;

class DateTimeTransformerTest extends TestCase
{
    private ?DateTimeInterface $dateTime;
    private ?DateTimeInterface $dateTimeWithoutSeconds;

    protected function setUp(): void
    {
        $this->dateTime = new DateTime('2010-02-03 04:05:06 UTC');
        $this->dateTimeWithoutSeconds = new DateTime('2010-02-03 04:05:00 UTC');
    }

    protected function tearDown(): void
    {
        $this->dateTime = null;
        $this->dateTimeWithoutSeconds = null;
    }

    public function transformProvider(): iterable
    {
        return [
            ['UTC', '2010-02-03 04:05:06 UTC', '2010-02-03T04:05:06Z'],
            ['UTC', null, ''],
            ['Asia/Hong_Kong', '2010-02-03 04:05:06 America/New_York', '2010-02-03T17:05:06+08:00'],
            ['Asia/Hong_Kong', null, ''],
            ['Asia/Hong_Kong', '2010-02-03 04:05:06 UTC', '2010-02-03T12:05:06+08:00'],
            ['UTC', '2010-02-03 04:05:06 America/New_York', '2010-02-03T09:05:06Z'],

            // format without seconds, as appears in some browsers
            ['UTC', '2010-02-03 04:05:00 UTC', '2010-02-03T04:05Z'],
            ['UTC', '2010-02-03 05:06:00 UTC', '2010-02-03T05:06+0000'],
            ['Asia/Hong_Kong', '2010-02-03 04:05:00 America/New_York', '2010-02-03T17:05+08:00'],
            ['Europe/Amsterdam', '2013-08-21 10:30:00 Europe/Amsterdam', '2013-08-21T08:30:00Z'],
        ];
    }

    /**
     * @dataProvider transformProvider
     */
    public function testTransform(string $toTz, ?string $to, string $from): void
    {
        $transformer = new DateTimeTransformer($toTz);

        if ($to !== null) {
            self::assertEquals(new DateTime($to), $transformer->transform($from));
        } else {
            self::assertNull($transformer->transform($from));
        }
    }

    public function testTransformMutability(): void
    {
        $transformer = new DateTimeTransformer('Etc/UTC', true);
        self::assertInstanceOf(\DateTimeImmutable::class, $transformer->transform(new \DateTime()));
        self::assertInstanceOf(\DateTimeImmutable::class, $transformer->transform(new \DateTimeImmutable()));

        $transformer = new DateTimeTransformer('Etc/UTC', false);
        self::assertInstanceOf(\DateTime::class, $transformer->transform(new \DateTime()));
        self::assertInstanceOf(\DateTime::class, $transformer->transform(new \DateTimeImmutable()));
    }

    public function testTransformRequiresString(): void
    {
        $this->expectException(TransformationFailedException::class);
        $transformer = new DateTimeTransformer();
        $transformer->transform(12345);
    }

    public function testTransformWithNonExistingDate(): void
    {
        $this->expectException(TransformationFailedException::class);
        $transformer = new DateTimeTransformer('UTC');

        $transformer->transform('2010-04-31T04:05Z');
    }

    /**
     * @dataProvider invalidDateStringProvider
     */
    public function testTransformExpectsValidDateString(string $date): void
    {
        $this->expectException(TransformationFailedException::class);
        $transformer = new DateTimeTransformer('UTC');

        $transformer->transform($date);
    }

    public function invalidDateStringProvider(): iterable
    {
        return [
            'invalid month' => ['2010-2010-01'],
            'invalid day' => ['2010-10-2010'],
            'no date' => ['x'],
            'cookie format' => ['Saturday, 01-May-2010 04:05:00 Z'],
            'RFC 822 format' => ['Sat, 01 May 10 04:05:00 +0000'],
            'RSS format' => ['Sat, 01 May 2010 04:05:00 +0000'],
        ];
    }
}
