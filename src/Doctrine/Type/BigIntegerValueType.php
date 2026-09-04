<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\Type;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Value\BigIntegerValue;

/** @extends BaseGenericType<BigIntegerValue> */
final class BigIntegerValueType extends BaseGenericType
{
    #[Override]
    protected static function getAbstractValueClass(): string
    {
        return BigIntegerValue::class;
    }

    #[Override]
    protected static function createDoctrineType(): Type
    {
        return new BigIntType();
    }
}
