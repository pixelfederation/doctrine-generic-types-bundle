<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Doctrine\TypeRegistry;

use Doctrine\DBAL\Types\TypeRegistry;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\TypeRegistryProvider as TypeRegistryProviderContract;

final readonly class TypeRegistryProvider implements TypeRegistryProviderContract
{
    private TypeRegistry $typeRegistry;

    public function __construct()
    {
        $this->typeRegistry = new TypeRegistry();
    }

    #[Override]
    public function provide(): TypeRegistry
    {
        return $this->typeRegistry;
    }
}
