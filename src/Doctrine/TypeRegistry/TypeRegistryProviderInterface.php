<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry;

use Doctrine\DBAL\Types\TypeRegistry;

interface TypeRegistryProviderInterface
{
    public function provide(): TypeRegistry;
}
