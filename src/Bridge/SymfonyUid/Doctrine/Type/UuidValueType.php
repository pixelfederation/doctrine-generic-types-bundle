<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Doctrine\Type;

use Doctrine\DBAL\Types\Type;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Value\UuidValue;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\BaseGenericType;
use Symfony\Bridge\Doctrine\Types\UuidType;

/**
 * @extends BaseGenericType<UuidValue>
 */
final class UuidValueType extends BaseGenericType
{
    /**
     * @return class-string<UuidValue>
     */
    #[Override]
    protected static function getAbstractValueClass(): string
    {
        return UuidValue::class;
    }

    #[Override]
    protected static function createDoctrineType(): Type
    {
        return new UuidType();
    }
}
