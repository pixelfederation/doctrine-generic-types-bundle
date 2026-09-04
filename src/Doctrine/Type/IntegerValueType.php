<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\Type;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Value\IntegerValue;

/**
 * @extends BaseGenericType<IntegerValue>
 */
final class IntegerValueType extends BaseGenericType
{
    /**
     * @return class-string<IntegerValue>
     */
    #[Override]
    protected static function getAbstractValueClass(): string
    {
        return IntegerValue::class;
    }

    #[Override]
    protected static function createDoctrineType(): Type
    {
        return new IntegerType();
    }
}
