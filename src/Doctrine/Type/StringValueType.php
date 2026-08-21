<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Value\StringValue;

/**
 * @extends BaseGenericType<StringValue>
 */
final class StringValueType extends BaseGenericType
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    /**
     * @return class-string<StringValue>
     */
    #[Override]
    protected static function getAbstractValueClass(): string
    {
        return StringValue::class;
    }
}
