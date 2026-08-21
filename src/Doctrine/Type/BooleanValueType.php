<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Value\BooleanValue;

/**
 * @extends BaseGenericType<BooleanValue>
 */
final class BooleanValueType extends BaseGenericType
{
    /**
     * @inheritdoc
     */
    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getBooleanTypeDeclarationSQL($column);
    }

    #[Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        $dbValue = parent::convertToDatabaseValue($value, $platform);
        if ($dbValue === null) {
            return null;
        }

        return $platform->convertBooleansToDatabaseValue($dbValue);
    }

    #[Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return parent::convertToPHPValue($platform->convertFromBoolean($value), $platform);
    }

    #[Override]
    public function getBindingType(): ParameterType
    {
        return ParameterType::BOOLEAN;
    }

    /**
     * @return class-string<BooleanValue>
     */
    #[Override]
    protected static function getAbstractValueClass(): string
    {
        return BooleanValue::class;
    }
}
