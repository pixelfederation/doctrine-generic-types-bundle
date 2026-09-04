<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Value\StringValue;

/**
 * @extends BaseGenericType<StringValue>
 */
final class StringValueType extends BaseGenericType
{
    /**
     * @return class-string<StringValue>
     */
    #[Override]
    protected static function getAbstractValueClass(): string
    {
        return StringValue::class;
    }

    #[Override]
    protected static function createDoctrineType(): Type
    {
        return new StringType();
    }
}
