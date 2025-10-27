<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Doctrine\Type;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Override;
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
    public function getName(): string
    {
        return HeightInCm::class;
    }

    #[Override]
    public function getBindingType(): int
    {
        return ParameterType::INTEGER;
    }
}
