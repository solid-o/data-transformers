<?php

declare(strict_types=1);

namespace Solido\DataTransformers;

use LogicException;
use ReflectionMethod;
use ReflectionProperty;
use Solido\DataTransformers\Annotation\Transform;
use Solido\DtoManagement\Proxy\Builder\Interceptor;
use Solido\DtoManagement\Proxy\Builder\ProxyBuilder;
use Solido\DtoManagement\Proxy\Extension\ExtensionInterface;

use function assert;
use function class_exists;
use function is_subclass_of;
use function sprintf;

class TransformerExtension implements ExtensionInterface
{
    public function extend(ProxyBuilder $proxyBuilder): void
    {
        foreach ($proxyBuilder->properties->getAccessibleProperties() as $property) {
            $transform = $this->getPropertyAttribute($property);
            if ($transform === null) {
                continue;
            }

            $this->assertExists($transform->transformer);
            $proxyBuilder->addPropertyInterceptor($property->getName(), new Interceptor($this->generateCode($transform->transformer, 'value')));
        }

        foreach ($proxyBuilder->class->getMethods() as $reflectionMethod) {
            $transform = $this->getMethodAttribute($reflectionMethod);
            if ($transform === null) {
                continue;
            }

            if ($reflectionMethod->isPrivate()) {
                throw new LogicException(sprintf('Method %s is private: cannot apply Transform attribute.', $reflectionMethod->getName()));
            }

            if ($reflectionMethod->isFinal()) {
                throw new LogicException(sprintf('Method %s is final: cannot apply Transform attribute.', $reflectionMethod->getName()));
            }

            if ($reflectionMethod->getNumberOfParameters() !== 1) {
                throw new LogicException(sprintf('Method %s requires %d parameters, but Transform annotation can be used only on single-argument method.', $reflectionMethod->getName(), $reflectionMethod->getNumberOfParameters()));
            }

            $proxyBuilder->addMethodInterceptor($reflectionMethod->getName(), new Interceptor($this->generateCode($transform->transformer, $reflectionMethod->getParameters()[0]->getName())));
        }
    }

    protected function generateCode(string $transformer, string $parameterName): string
    {
        return sprintf('$transformer = new \%s(); $%s = $transformer->transform($%s);', $transformer, $parameterName, $parameterName);
    }

    protected function assertExists(string $transformer): void
    {
        if (! class_exists($transformer)) {
            throw new LogicException(sprintf('Transformer class "%s" does not exist.', $transformer));
        }

        if (! is_subclass_of($transformer, TransformerInterface::class)) {
            throw new LogicException(sprintf('Transformer "%s" does not implement "%s".', $transformer, TransformerInterface::class));
        }
    }

    private function getPropertyAttribute(ReflectionProperty $property): Transform|null
    {
        foreach ($property->getAttributes(Transform::class) as $attribute) {
            $transform = $attribute->newInstance();
            assert($transform instanceof Transform);

            return $transform;
        }

        return null;
    }

    private function getMethodAttribute(ReflectionMethod $method): Transform|null
    {
        foreach ($method->getAttributes(Transform::class) as $attribute) {
            $transform = $attribute->newInstance();
            assert($transform instanceof Transform);

            return $transform;
        }

            return null;
    }
}
