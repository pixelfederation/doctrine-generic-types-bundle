<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Doctrine\Type;

use Doctrine\DBAL\Types\Type;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Value\UlidValue;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\BaseGenericType;
use Symfony\Bridge\Doctrine\Types\UlidType;

/**
 * @extends BaseGenericType<UlidValue>
 */
final class UlidValueType extends BaseGenericType
{
    /**
     * @return class-string<UlidValue>
     */
    #[Override]
    protected static function getAbstractValueClass(): string
    {
        return UlidValue::class;
    }

    #[Override]
    protected static function createDoctrineType(): Type
    {
        return new UlidType();
    }
}
