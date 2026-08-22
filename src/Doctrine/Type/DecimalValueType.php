<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\Types\DecimalType;
use Doctrine\DBAL\Types\Type;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Value\DecimalValue;

/** @extends BaseGenericType<DecimalValue> */
final class DecimalValueType extends BaseGenericType
{
    #[Override]
    protected static function getAbstractValueClass(): string
    {
        return DecimalValue::class;
    }

    #[Override]
    protected static function createDoctrineType(): Type
    {
        return new DecimalType();
    }
}
