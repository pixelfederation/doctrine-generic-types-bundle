<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry;

use LogicException;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\GenericType;

final readonly class GenericTypeFactoryProvider
{
    public const string TAG = 'pixel_federation.doctrine_generic_types.generic_type_factory';

    public const int DEFAULT_PRIORITY = 0;

    /**
     * @param iterable<GenericTypeFactory> $factories
     */
    public function __construct(
        private iterable $factories,
    ) {
    }

    /**
     * @param class-string<GenericType> $type
     */
    public function provide(string $type): GenericTypeFactory
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($type)) {
                return $factory;
            }
        }

        throw new LogicException(sprintf('No generic type factory supports Doctrine type "%s".', $type));
    }
}
