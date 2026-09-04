<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Doctrine\TypeRegistry;

use Doctrine\DBAL\Types\StringType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\GenericType;

final class UnsupportedGenericType extends StringType implements GenericType
{
}
