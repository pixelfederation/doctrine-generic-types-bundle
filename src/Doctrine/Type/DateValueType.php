<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\Types\DateImmutableType;
use Doctrine\DBAL\Types\Type;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Value\DateValue;

/**
 * @extends BaseGenericType<DateValue>
 */
final class DateValueType extends BaseGenericType
{
    /**
     * @return class-string<DateValue>
     */
    #[Override]
    protected static function getAbstractValueClass(): string
    {
        return DateValue::class;
    }

    #[Override]
    protected static function createDoctrineType(): Type
    {
        return new DateImmutableType();
    }
}
