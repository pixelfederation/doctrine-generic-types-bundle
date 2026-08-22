<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\Types\Type;
use PixelFederation\DoctrineGenericTypesBundle\Value\Value;

interface StaticGenericType extends GenericType
{
    /**
     * @param class-string<Value<mixed>> $class
     */
    public static function createForValue(string $class): Type;
}
