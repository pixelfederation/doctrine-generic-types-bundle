<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\Types\Type;

interface GenericType
{
    /**
     * @param class-string $class
     */
    public static function createForValue(string $class): Type;
}
