<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\Types\TimeImmutableType;
use Doctrine\DBAL\Types\Type;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Value\TimeValue;

/** @extends BaseGenericType<TimeValue> */
final class TimeValueType extends BaseGenericType
{
    /** @return class-string<TimeValue> */
    #[Override]
    protected static function getAbstractValueClass(): string
    {
        return TimeValue::class;
    }

    #[Override]
    protected static function createDoctrineType(): Type
    {
        return new TimeImmutableType();
    }
}
