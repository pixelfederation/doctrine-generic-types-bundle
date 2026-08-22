<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Doctrine\Type;

use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Doctrine\Type\UlidChar26Type;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Doctrine\Type\UlidValueChar26Type;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Doctrine\Type\UlidValueType;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Value\UlidValue;
use PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Value\TestSymfonyUlidValue;
use Symfony\Component\Uid\Ulid;

#[CoversClass(UlidChar26Type::class)]
#[CoversClass(UlidValue::class)]
#[CoversClass(UlidValueChar26Type::class)]
#[CoversClass(UlidValueType::class)]
final class SymfonyUlidValueTypeTest extends TestCase
{
    private const string ULID = '01E439TP9XJZ9RPFH3T1PYBCR8';

    public function testSymfonyUlidTypeConversion(): void
    {
        $platform = new SQLitePlatform();
        $type = UlidValueType::createForValue(TestSymfonyUlidValue::class);
        $value = new TestSymfonyUlidValue(Ulid::fromString(self::ULID));
        $databaseValue = $type->convertToDatabaseValue($value, $platform);

        self::assertIsString($databaseValue);
        self::assertSame(16, strlen($databaseValue));
        self::assertEquals($value, $type->convertToPHPValue($databaseValue, $platform));
        self::assertSame('BLOB', $type->getSQLDeclaration([], $platform));
        self::assertSame(self::ULID, (string) $value);
    }

    public function testChar26UlidTypeConversion(): void
    {
        $platform = new SQLitePlatform();
        $type = UlidValueChar26Type::createForValue(TestSymfonyUlidValue::class);
        $value = new TestSymfonyUlidValue(Ulid::fromString(self::ULID));

        self::assertSame(self::ULID, $type->convertToDatabaseValue($value, $platform));
        self::assertEquals($value, $type->convertToPHPValue(strtolower(self::ULID), $platform));
        self::assertSame('CHAR(26)', $type->getSQLDeclaration([], $platform));
    }

    public function testChar26UlidTypeRejectsInvalidFormat(): void
    {
        $type = UlidValueChar26Type::createForValue(TestSymfonyUlidValue::class);

        $this->expectException(InvalidFormat::class);

        $type->convertToPHPValue('invalid', new SQLitePlatform());
    }
}
