<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\Type;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Value\BooleanValue;

/**
 * @extends BaseGenericType<BooleanValue>
 */
final class BooleanValueType extends BaseGenericType
{
    /**
     * @return class-string<BooleanValue>
     */
    #[Override]
    protected static function getAbstractValueClass(): string
    {
        return BooleanValue::class;
    }

    #[Override]
    protected static function createDoctrineType(): Type
    {
        return new BooleanType();
    }
}
