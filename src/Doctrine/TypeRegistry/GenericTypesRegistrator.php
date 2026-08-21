<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry;

use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\GenericType;
use PixelFederation\DoctrineGenericTypesBundle\Value\Value;

final readonly class GenericTypesRegistrator
{
    /**
     * @param array<class-string<Value<mixed>>, class-string<GenericType>> $genericTypesMapping
     */
    public function __construct(
        private TypeRegistryProviderInterface $typeRegistryProvider,
        private array $genericTypesMapping = [],
    ) {
    }

    public function register(): void
    {
        $typeRegistry = $this->typeRegistryProvider->provide();
        foreach ($this->genericTypesMapping as $value => $type) {
            if ($typeRegistry->has($value)) {
                continue;
            }

            $typeRegistry->register($value, $type::createForValue($value));
        }
    }
}
