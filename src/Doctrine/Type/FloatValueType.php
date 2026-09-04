<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\Type;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Value\FloatValue;

/**
 * @extends BaseGenericType<FloatValue>
 */
final class FloatValueType extends BaseGenericType
{
    /**
     * @return class-string<FloatValue>
     */
    #[Override]
    protected static function getAbstractValueClass(): string
    {
        return FloatValue::class;
    }

    #[Override]
    protected static function createDoctrineType(): Type
    {
        return new FloatType();
    }
}
