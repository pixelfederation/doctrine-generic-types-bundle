<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Doctrine\TypeRegistry;

use Doctrine\DBAL\Types\TypeRegistry;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\TypeRegistryProviderInterface;

final readonly class TypeRegistryProvider implements TypeRegistryProviderInterface
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
