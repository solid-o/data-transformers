<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Tests;

use DateTimeInterface;
use LogicException;
use PHPUnit\Framework\TestCase;
use Solido\DataTransformers\Tests\Fixtures\ProxableClass;
use Solido\DataTransformers\Tests\Fixtures\ProxableClassFooBar;
use Solido\DataTransformers\Tests\Fixtures\ProxableClassWithAttributes;
use Solido\DataTransformers\Tests\Fixtures\ProxableClassWithBadTransformer;
use Solido\DataTransformers\Tests\Fixtures\ProxableClassWithBadTransformer2;
use Solido\DataTransformers\Tests\Fixtures\ProxableClassWithFinalMethod;
use Solido\DataTransformers\Tests\Fixtures\ProxableClassWithMultipleParams;
use Solido\DataTransformers\Tests\Fixtures\ProxableClassWithNonExistentTransformer;
use Solido\DataTransformers\Tests\Fixtures\ProxableClassWithPrivateMethod;
use Solido\DataTransformers\TransformerExtension;
use Solido\DtoManagement\Proxy\Factory\AccessInterceptorFactory;
use Solido\DtoManagement\Proxy\Factory\Configuration;
use function Safe\sprintf;

class TransformerExtensionTest extends TestCase
{
    private TransformerExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new TransformerExtension();
    }

    public function testBuildProxyInterceptors(): void
    {
        $configuration = new Configuration();
        $configuration->addExtension($this->extension);

        $factory = new AccessInterceptorFactory($configuration);
        $className = $factory->generateProxy(ProxableClass::class);

        $obj = new $className();
        $obj->boolean = '1';
        $obj->dateTime = '2020-05-18T00:00:00Z';
        $obj->setNewBool('on');

        self::assertIsBool($obj->boolean);
        self::assertTrue($obj->boolean);

        self::assertIsBool($obj->newBool);
        self::assertTrue($obj->newBool);

        self::assertInstanceOf(DateTimeInterface::class, $obj->dateTime);
    }

    public function testShouldCheckTransformerExistence(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Transformer class "NonExistent" does not exist.');

        $configuration = new Configuration();
        $configuration->addExtension($this->extension);

        $factory = new AccessInterceptorFactory($configuration);
        $factory->generateProxy(ProxableClassWithNonExistentTransformer::class);
    }

    public function testShouldCheckTransformerImplementsTransformerInterface(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Transformer "stdClass" does not implement "Solido\DataTransformers\TransformerInterface".');

        $configuration = new Configuration();
        $configuration->addExtension($this->extension);

        $factory = new AccessInterceptorFactory($configuration);
        $factory->generateProxy(ProxableClassWithBadTransformer::class);
    }

    public function testExistenceCheckCouldBeOverridden(): void
    {
        $this->expectNotToPerformAssertions();

        $configuration = new Configuration();
        $configuration->addExtension(new class extends TransformerExtension {
            protected function assertExists(string $transformer): void
            {
                // Do nothing.
            }
        });

        $factory = new AccessInterceptorFactory($configuration);
        $factory->generateProxy(ProxableClassWithBadTransformer2::class);
    }

    public function testCodeGenerationCouldBeOverridden(): void
    {
        $configuration = new Configuration();
        $configuration->addExtension(new class extends TransformerExtension {
            protected function generateCode(string $transformer, string $parameterName): string
            {
                return sprintf('$%s = "foobar";', $parameterName);
            }
        });

        $factory = new AccessInterceptorFactory($configuration);
        $className = $factory->generateProxy(ProxableClassFooBar::class);

        $obj = new $className();
        assert($obj instanceof ProxableClassFooBar);

        $obj->shouldString = new \stdClass();
        self::assertEquals('foobar', $obj->shouldString);
    }

    public function testShouldThrowOnPrivateMethods(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Method setBool is private: cannot apply Transform attribute.');

        $configuration = new Configuration();
        $configuration->addExtension($this->extension);

        $factory = new AccessInterceptorFactory($configuration);
        $factory->generateProxy(ProxableClassWithPrivateMethod::class);
    }

    public function testShouldThrowOnFinalMethods(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Method setBool is final: cannot apply Transform attribute.');

        $configuration = new Configuration();
        $configuration->addExtension($this->extension);

        $factory = new AccessInterceptorFactory($configuration);
        $factory->generateProxy(ProxableClassWithFinalMethod::class);
    }

    public function testShouldThrowOnMethodsWithMultipleParameters(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Method setNewBool requires 2 parameters, but Transform annotation can be used only on single-argument method.');

        $configuration = new Configuration();
        $configuration->addExtension($this->extension);

        $factory = new AccessInterceptorFactory($configuration);
        $factory->generateProxy(ProxableClassWithMultipleParams::class);
    }
}
