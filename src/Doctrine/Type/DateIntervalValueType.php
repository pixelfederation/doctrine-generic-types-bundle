<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\Types\DateIntervalType;
use Doctrine\DBAL\Types\Type;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Value\DateIntervalValue;

/** @extends BaseGenericType<DateIntervalValue> */
final class DateIntervalValueType extends BaseGenericType
{
    /** @return class-string<DateIntervalValue> */
    #[Override]
    protected static function getAbstractValueClass(): string
    {
        return DateIntervalValue::class;
    }

    #[Override]
    protected static function createDoctrineType(): Type
    {
        return new DateIntervalType();
    }
}
