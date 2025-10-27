<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry;

use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\GenericType;
use PixelFederation\DoctrineGenericTypesBundle\Value\Value;

final class GenericTypesRegistrator
{
    private bool $registered = false;

    /**
     * @param array<class-string<Value<mixed>>, class-string<GenericType>> $genericTypesMapping
     */
    public function __construct(
        private readonly TypeRegistryProviderInterface $typeRegistryProvider,
        private readonly array $genericTypesMapping = [],
    ) {
    }

    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        $typeRegistry = $this->typeRegistryProvider->provide();
        foreach ($this->genericTypesMapping as $value => $type) {
            if ($typeRegistry->has($value)) {
                continue;
            }

            /**
             * @psalm-suppress InvalidArgument
             */
            $typeRegistry->register($value, $type::createForValue($value));
        }

        $this->registered = true;
    }
}
