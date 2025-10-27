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
     * @psalm-suppress InvalidClassConstantType
     */
    protected const string ABSTRACT_VALUE = BooleanValue::class;

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
        /**
         * @psalm-suppress MixedAssignment
         */
        $dbValue = parent::convertToDatabaseValue($value, $platform);
        if ($dbValue === null) {
            return null;
        }

        return $platform->convertBooleans($dbValue);
    }

    #[Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return parent::convertToPHPValue($platform->convertFromBoolean($value), $platform);
    }

    #[Override]
    public function getBindingType(): int
    {
        return ParameterType::BOOLEAN;
    }
}
