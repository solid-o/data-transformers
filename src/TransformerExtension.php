<?php

declare(strict_types=1);

namespace Solido\DataTransformers;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use LogicException;
use Solido\DataTransformers\Annotation\Transform;
use Solido\DtoManagement\Proxy\Builder\Interceptor;
use Solido\DtoManagement\Proxy\Builder\ProxyBuilder;
use Solido\DtoManagement\Proxy\Extension\ExtensionInterface;
use function assert;
use function class_exists;
use function is_subclass_of;
use function Safe\sprintf;

class TransformerExtension implements ExtensionInterface
{
    private Reader $reader;

    public function __construct(?Reader $reader = null)
    {
        $this->reader = $reader ?? new AnnotationReader();
    }

    public function extend(ProxyBuilder $proxyBuilder): void
    {
        foreach ($proxyBuilder->properties->getAccessibleProperties() as $property) {
            $transform = $this->reader->getPropertyAnnotation($property, Transform::class);
            if ($transform === null) {
                continue;
            }

            assert($transform instanceof Transform);

            $this->assertExists($transform->transformer);
            $proxyBuilder->addPropertyInterceptor($property->getName(), new Interceptor($this->generateCode($transform->transformer, 'value')));
        }

        foreach ($proxyBuilder->class->getMethods() as $reflectionMethod) {
            if ($reflectionMethod->isPrivate() || $reflectionMethod->isFinal()) {
                continue;
            }

            $transform = $this->reader->getMethodAnnotation($reflectionMethod, Transform::class);
            if ($transform === null) {
                continue;
            }

            if ($reflectionMethod->getNumberOfParameters() !== 1) {
                throw new LogicException(sprintf('Method %s requires %d parameters, but Transform annotation can be used only on single-argument method.', $reflectionMethod->getName(), $reflectionMethod->getNumberOfParameters()));
            }

            assert($transform instanceof Transform);
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
}
