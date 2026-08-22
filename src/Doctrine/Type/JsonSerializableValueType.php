<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\Type;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Value\JsonSerializableValue;

/** @extends BaseGenericType<JsonSerializableValue> */
final class JsonSerializableValueType extends BaseGenericType
{
    #[Override]
    protected static function getAbstractValueClass(): string
    {
        return JsonSerializableValue::class;
    }

    #[Override]
    protected static function createDoctrineType(): Type
    {
        return new JsonType();
    }
}
