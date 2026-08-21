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
     * @inheritDoc
     */
    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getIntegerTypeDeclarationSQL($column);
    }

    #[Override]
    public function getBindingType(): ParameterType
    {
        return ParameterType::INTEGER;
    }

    /**
     * @return class-string<IntegerValue>
     */
    #[Override]
    protected static function getAbstractValueClass(): string
    {
        return IntegerValue::class;
    }
}
