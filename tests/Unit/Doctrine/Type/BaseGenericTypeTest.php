<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Doctrine\Type;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\RamseyUuid\Doctrine\Type\UuidValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\BaseGenericType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\BooleanValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\IntegerValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\StringValueType;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidDatabaseTypeException;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidValueFormatException;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\Count;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\FirstName;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\IsActive;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\UserId;

#[CoversClass(BaseGenericType::class)]
#[CoversClass(BooleanValueType::class)]
#[CoversClass(IntegerValueType::class)]
#[CoversClass(StringValueType::class)]
#[CoversClass(UuidValueType::class)]
#[CoversClass(InvalidDatabaseTypeException::class)]
#[CoversClass(InvalidValueFormatException::class)]
final class BaseGenericTypeTest extends TestCase
{
    public function testNullConversion(): void
    {
        $type = StringValueType::createForValue(FirstName::class);
        $platform = new SQLitePlatform();

        self::assertNull($type->convertToDatabaseValue(null, $platform));
        self::assertNull($type->convertToPHPValue(null, $platform));
    }

    public function testInvalidPHPValueConversion(): void
    {
        $type = StringValueType::createForValue(FirstName::class);

        $this->expectException(InvalidType::class);
        $type->convertToDatabaseValue(new Count(1), new SQLitePlatform());
    }

    public function testInvalidDatabaseValueConversion(): void
    {
        $type = StringValueType::createForValue(FirstName::class);

        try {
            $type->convertToPHPValue(1, new SQLitePlatform());
            self::fail('Invalid database value must throw a conversion exception.');
        } catch (InvalidType $exception) {
            self::assertInstanceOf(InvalidDatabaseTypeException::class, $exception->getPrevious());
        }
    }

    public function testBooleanConversion(): void
    {
        $type = BooleanValueType::createForValue(IsActive::class);
        $platform = new SQLitePlatform();

        self::assertSame(1, $type->convertToDatabaseValue(new IsActive(true), $platform));
        self::assertEquals(new IsActive(true), $type->convertToPHPValue(1, $platform));
        self::assertSame(0, $type->convertToDatabaseValue(new IsActive(false), $platform));
        self::assertEquals(new IsActive(false), $type->convertToPHPValue(0, $platform));
    }

    public function testBindingTypes(): void
    {
        self::assertSame(
            ParameterType::BOOLEAN,
            BooleanValueType::createForValue(IsActive::class)->getBindingType(),
        );
        self::assertSame(
            ParameterType::INTEGER,
            IntegerValueType::createForValue(Count::class)->getBindingType(),
        );
    }

    public function testStringAndUuidSQLDeclarations(): void
    {
        $platform = new SQLitePlatform();

        self::assertSame(
            'VARCHAR(255)',
            StringValueType::createForValue(FirstName::class)->getSQLDeclaration(['length' => 255], $platform),
        );
        self::assertSame(
            'CHAR(36)',
            UuidValueType::createForValue(UserId::class)->getSQLDeclaration([], $platform),
        );
    }

    public function testIncompatibleValueClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        BooleanValueType::createForValue(FirstName::class);
    }

    public function testInvalidValueFormatConversionException(): void
    {
        $exception = new InvalidValueFormatException('invalid', FirstName::class, 'non-empty string');
        $conversionException = $exception->toConversionException();

        self::assertInstanceOf(InvalidFormat::class, $conversionException);
        self::assertSame($exception, $conversionException->getPrevious());
    }
}
