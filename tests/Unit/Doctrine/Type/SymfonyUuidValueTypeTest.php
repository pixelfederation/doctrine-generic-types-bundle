<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Doctrine\Type;

use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Doctrine\Type\UuidChar36Type;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Doctrine\Type\UuidValueChar36Type;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Doctrine\Type\UuidValueType;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Value\UuidValue;
use PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Value\TestSymfonyUuidValue;
use Symfony\Component\Uid\Uuid;

#[CoversClass(UuidChar36Type::class)]
#[CoversClass(UuidValue::class)]
#[CoversClass(UuidValueChar36Type::class)]
#[CoversClass(UuidValueType::class)]
final class SymfonyUuidValueTypeTest extends TestCase
{
    private const string UUID = '550e8400-e29b-41d4-a716-446655440000';

    public function testSymfonyUuidTypeConversion(): void
    {
        $platform = new SQLitePlatform();
        $type = UuidValueType::createForValue(TestSymfonyUuidValue::class);
        $value = new TestSymfonyUuidValue(Uuid::fromString(self::UUID));
        $databaseValue = $type->convertToDatabaseValue($value, $platform);

        self::assertIsString($databaseValue);
        self::assertSame(16, strlen($databaseValue));
        self::assertEquals($value, $type->convertToPHPValue($databaseValue, $platform));
        self::assertSame('BLOB', $type->getSQLDeclaration([], $platform));
        self::assertSame(self::UUID, (string) $value);
    }

    public function testChar36UuidTypeConversion(): void
    {
        $platform = new SQLitePlatform();
        $type = UuidValueChar36Type::createForValue(TestSymfonyUuidValue::class);
        $value = new TestSymfonyUuidValue(Uuid::fromString(self::UUID));

        self::assertSame(
            self::UUID,
            $type->convertToDatabaseValue($value, $platform),
        );
        self::assertEquals(
            $value,
            $type->convertToPHPValue(strtoupper(self::UUID), $platform),
        );
        self::assertSame('CHAR(36)', $type->getSQLDeclaration([], $platform));
    }

    public function testChar36UuidTypeRejectsInvalidFormat(): void
    {
        $type = UuidValueChar36Type::createForValue(TestSymfonyUuidValue::class);

        $this->expectException(InvalidFormat::class);

        $type->convertToPHPValue('550e8400e29b41d4a716446655440000', new SQLitePlatform());
    }
}
