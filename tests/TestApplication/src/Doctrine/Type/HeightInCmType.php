<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Doctrine\Type;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Type;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidValueException;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\ValueWithoutGenericType\HeightInCm;

final class HeightInCmType extends Type
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getIntegerTypeDeclarationSQL($column);
    }

    #[Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?int
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof HeightInCm) {
            throw InvalidType::new($value, HeightInCm::class, ['null', HeightInCm::class]);
        }

        return $value->toDbValue();
    }

    #[Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?HeightInCm
    {
        if ($value === null) {
            return null;
        }

        try {
            return HeightInCm::fromDbValue($value);
        } catch (InvalidValueException $e) {
            throw $e->toConversionException();
        }
    }

    #[Override]
    public function getBindingType(): ParameterType
    {
        return ParameterType::INTEGER;
    }
}
