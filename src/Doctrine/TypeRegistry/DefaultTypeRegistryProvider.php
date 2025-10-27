<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\TypeRegistry;
use Override;

final class DefaultTypeRegistryProvider implements TypeRegistryProviderInterface
{
    #[Override]
    public function provide(): TypeRegistry
    {
        return Type::getTypeRegistry();
    }
}
