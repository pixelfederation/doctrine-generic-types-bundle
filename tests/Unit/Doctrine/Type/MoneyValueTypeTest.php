<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Doctrine\Type;

use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\CustomValue\Price;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Doctrine\Type\MoneyValueType;

#[CoversNothing]
final class MoneyValueTypeTest extends TestCase
{
    public function testNonStringDatabaseValueUsesValueClassAsConversionTarget(): void
    {
        $type = MoneyValueType::createForValue(Price::class);

        $this->expectException(InvalidType::class);
        $this->expectExceptionMessage(Price::class);

        $type->convertToPHPValue(1, new SQLitePlatform());
    }

    public function testInvalidJsonUsesValueClassAsConversionTarget(): void
    {
        $type = MoneyValueType::createForValue(Price::class);

        $this->expectException(ValueNotConvertible::class);
        $this->expectExceptionMessage(Price::class);

        $type->convertToPHPValue('{', new SQLitePlatform());
    }

    public function testScalarJsonIsRejectedExplicitly(): void
    {
        $type = MoneyValueType::createForValue(Price::class);

        $this->expectException(InvalidFormat::class);
        $this->expectExceptionMessage(Price::class);

        $type->convertToPHPValue('1', new SQLitePlatform());
    }
}
