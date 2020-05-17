<?php declare(strict_types=1);

namespace Solido\DataTransformers\Tests\Transformer;

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use PHPUnit\Framework\TestCase;
use Solido\DataTransformers\Exception\TransformationFailedException;
use Solido\DataTransformers\Transformer\PhoneNumberTransformer;

class PhoneNumberToStringTransformerTest extends TestCase
{
    private PhoneNumberTransformer $transformer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->transformer = new PhoneNumberTransformer();
    }

    public function testTransformShouldReturnEmptyStringOnNull(): void
    {
        self::assertEquals('', $this->transformer->transform(null));
    }

    public function nonPhoneNumberArguments(): iterable
    {
        yield ['i am not a phone number'];
        yield [new \stdClass()];
        yield [[]];
        yield [123];
        yield [11.123];
        yield ['+393939898231'];
    }

    public function testReverseTransformShouldReturnNullOnEmptyString(): void
    {
        self::assertEquals(null, $this->transformer->transform(''));
    }

    public function nonPhoneNumberStringRepresentation(): iterable
    {
        yield ['i am not a phone number'];
        yield [new \stdClass()];
        yield [[]];
        yield [123];
        yield [11.123];
    }

    /**
     * @dataProvider nonPhoneNumberStringRepresentation
     */
    public function testReverseTransformShouldThrowOnNonPhoneNumberStringRepresentation($argument): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->transformer->transform($argument);
    }

    public function testReverseTransformShouldReturnPhoneNumber(): void
    {
        $phone = '+393939898231';

        $phoneNumber = $this->transformer->transform($phone);
        self::assertInstanceOf(PhoneNumber::class, $phoneNumber);
        self::assertEquals($phone, PhoneNumberUtil::getInstance()->format($phoneNumber, PhoneNumberFormat::E164));
    }
}
