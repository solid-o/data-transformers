<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Tests;

use DateTimeInterface;
use LogicException;
use PHPUnit\Framework\TestCase;
use Solido\DataTransformers\Tests\Fixtures\ProxableClass;
use Solido\DataTransformers\Tests\Fixtures\ProxableClassWithAttributes;
use Solido\DataTransformers\Tests\Fixtures\ProxableClassWithBadTransformer;
use Solido\DataTransformers\Tests\Fixtures\ProxableClassWithNonExistentTransformer;
use Solido\DataTransformers\TransformerExtension;
use Solido\DtoManagement\Proxy\Factory\AccessInterceptorFactory;
use Solido\DtoManagement\Proxy\Factory\Configuration;

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

    /**
     * @requires PHP >= 8.0
     */
    public function testReadTransformAttributes(): void
    {
        $configuration = new Configuration();
        $configuration->addExtension($this->extension);

        $factory = new AccessInterceptorFactory($configuration);
        $className = $factory->generateProxy(ProxableClassWithAttributes::class);

        $obj = new $className();
        $obj->boolean = '1';
        $obj->dateTime = '2020-05-18T00:00:00Z';

        self::assertIsBool($obj->boolean);
        self::assertTrue($obj->boolean);
        self::assertInstanceOf(DateTimeInterface::class, $obj->dateTime);
    }

    public function testShouldCheckTransformerExistence(): void
    {
        $this->expectException(LogicException::class);
        $this->expectDeprecationMessage('Transformer class "NonExistent" does not exist.');

        $configuration = new Configuration();
        $configuration->addExtension($this->extension);

        $factory = new AccessInterceptorFactory($configuration);
        $factory->generateProxy(ProxableClassWithNonExistentTransformer::class);
    }

    public function testShouldCheckTransformerImplementsTransformerInterface(): void
    {
        $this->expectException(LogicException::class);
        $this->expectDeprecationMessage('Transformer "stdClass" does not implement "Solido\DataTransformers\TransformerInterface".');

        $configuration = new Configuration();
        $configuration->addExtension($this->extension);

        $factory = new AccessInterceptorFactory($configuration);
        $factory->generateProxy(ProxableClassWithBadTransformer::class);
    }
}
