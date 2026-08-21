<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Bridge\RamseyUuid\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\RamseyUuid\Value\UuidValue;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\BaseGenericType;

/**
 * @extends BaseGenericType<UuidValue>
 */
final class UuidValueType extends BaseGenericType
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $column['length'] = 36;
        $column['fixed'] = true;

        return $platform->getStringTypeDeclarationSQL($column);
    }

    /**
     * @return class-string<UuidValue>
     */
    #[Override]
    protected static function getAbstractValueClass(): string
    {
        return UuidValue::class;
    }
}
