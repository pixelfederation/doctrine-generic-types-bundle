<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\Type;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Value\DateTimeValue;

/**
 * @extends BaseGenericType<DateTimeValue>
 */
final class DateTimeValueType extends BaseGenericType
{
    /**
     * @return class-string<DateTimeValue>
     */
    #[Override]
    protected static function getAbstractValueClass(): string
    {
        return DateTimeValue::class;
    }

    #[Override]
    protected static function createDoctrineType(): Type
    {
        return new DateTimeImmutableType();
    }
}
