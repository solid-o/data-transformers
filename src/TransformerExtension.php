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

            if (! class_exists($transform->transformer)) {
                throw new LogicException(sprintf('Transformer class "%s" does not exist.', $transform->transformer));
            }

            $proxyBuilder->addPropertyInterceptor($property->getName(), new Interceptor($this->generateCode($transform->transformer)));
        }
    }

    protected function generateCode(string $transformer): string
    {
        return sprintf('$transformer = new \%s(); $value = $transformer->transform($value);', $transformer);
    }
}
