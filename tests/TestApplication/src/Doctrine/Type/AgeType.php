<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\OtherValue\Age;

final class AgeType extends IntegerType
{
    #[Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?int
    {
        if (!$value instanceof Age) {
            return null;
        }

        return $value->toDbValue();
    }

    #[Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Age
    {
        if (!is_int($value)) {
            return null;
        }

        return new Age($value);
    }

    #[Override]
    public function getName(): string
    {
        return Age::class;
    }
}
