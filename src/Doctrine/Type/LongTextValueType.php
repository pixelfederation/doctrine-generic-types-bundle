<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Types\Type;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Value\LongTextValue;

/** @extends BaseGenericType<LongTextValue> */
final class LongTextValueType extends BaseGenericType
{
    #[Override]
    protected static function getAbstractValueClass(): string
    {
        return LongTextValue::class;
    }

    #[Override]
    protected static function createDoctrineType(): Type
    {
        return new TextType();
    }
}
