<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Value\IntegerValue;

/**
 * @extends BaseGenericType<IntegerValue>
 */
final class IntegerValueType extends BaseGenericType
{
    /**
     * @psalm-suppress InvalidClassConstantType
     */
    protected const string ABSTRACT_VALUE = IntegerValue::class;

    /**
     * @inheritDoc
     */
    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getIntegerTypeDeclarationSQL($column);
    }

    #[Override]
    public function getBindingType(): int
    {
        return ParameterType::INTEGER;
    }
}
