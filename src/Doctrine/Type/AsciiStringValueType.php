<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\Types\AsciiStringType;
use Doctrine\DBAL\Types\Type;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Value\AsciiStringValue;

/** @extends BaseGenericType<AsciiStringValue> */
final class AsciiStringValueType extends BaseGenericType
{
    /** @return class-string<AsciiStringValue> */
    #[Override]
    protected static function getAbstractValueClass(): string
    {
        return AsciiStringValue::class;
    }

    #[Override]
    protected static function createDoctrineType(): Type
    {
        return new AsciiStringType();
    }
}
