<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Value\FloatValue;

/**
 * @extends BaseGenericType<FloatValue>
 */
final class FloatValueType extends BaseGenericType
{
    /**
     * @psalm-suppress InvalidClassConstantType
     */
    protected const string ABSTRACT_VALUE = FloatValue::class;

    /**
     * @inheritdoc
     */
    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getFloatDeclarationSQL($column);
    }
}
