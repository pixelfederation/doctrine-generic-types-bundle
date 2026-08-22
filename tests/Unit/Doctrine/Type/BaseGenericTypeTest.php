<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Doctrine\Type;

use DateTimeImmutable;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\RamseyUuid\Doctrine\Type\UuidValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\BaseGenericType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\BigIntegerValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\BooleanValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\DateTimeValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\DateValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\DecimalValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\FloatValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\IntegerValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\JsonSerializableValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\LongTextValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\NativeJsonValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\StringValueType;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidDatabaseTypeException;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidValueFormatException;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\Count;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\FirstName;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\IsActive;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\UserId;
use PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Value\TestBigIntegerValue;
use PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Value\TestDateTimeValue;
use PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Value\TestDateValue;
use PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Value\TestDecimalValue;
use PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Value\TestFloatValue;
use PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Value\TestJsonSerializableValue;
use PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Value\TestLongTextValue;
use PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Value\TestNativeJsonValue;

#[CoversClass(BaseGenericType::class)]
#[CoversClass(BigIntegerValueType::class)]
#[CoversClass(BooleanValueType::class)]
#[CoversClass(DateTimeValueType::class)]
#[CoversClass(DateValueType::class)]
#[CoversClass(DecimalValueType::class)]
#[CoversClass(FloatValueType::class)]
#[CoversClass(IntegerValueType::class)]
#[CoversClass(JsonSerializableValueType::class)]
#[CoversClass(LongTextValueType::class)]
#[CoversClass(NativeJsonValueType::class)]
#[CoversClass(StringValueType::class)]
#[CoversClass(UuidValueType::class)]
#[CoversClass(InvalidDatabaseTypeException::class)]
#[CoversClass(InvalidValueFormatException::class)]
final class BaseGenericTypeTest extends TestCase
{
    public function testNullConversion(): void
    {
        $platform = new SQLitePlatform();

        $type = StringValueType::createForValue(FirstName::class);
        self::assertNull($type->convertToDatabaseValue(null, $platform));
        self::assertNull($type->convertToPHPValue(null, $platform));

        $booleanType = BooleanValueType::createForValue(IsActive::class);
        self::assertNull($booleanType->convertToDatabaseValue(null, $platform));
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

    public function testNumericDatabaseValuesUseDbalConversion(): void
    {
        $platform = new SQLitePlatform();

        self::assertEquals(
            new Count(12),
            IntegerValueType::createForValue(Count::class)->convertToPHPValue('12', $platform),
        );
        self::assertEquals(
            new TestFloatValue(12.5),
            FloatValueType::createForValue(TestFloatValue::class)->convertToPHPValue('12.5', $platform),
        );
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

    public function testAdditionalTypeConversions(): void
    {
        $platform = new SQLitePlatform();

        $decimalType = DecimalValueType::createForValue(TestDecimalValue::class);
        self::assertSame('12.50', $decimalType->convertToDatabaseValue(new TestDecimalValue('12.50'), $platform));
        self::assertEquals(new TestDecimalValue('12.5'), $decimalType->convertToPHPValue(12.5, $platform));
        self::assertSame(
            'NUMERIC(10, 2)',
            $decimalType->getSQLDeclaration(['precision' => 10, 'scale' => 2], $platform),
        );

        $bigIntegerType = BigIntegerValueType::createForValue(TestBigIntegerValue::class);
        self::assertSame(12, $bigIntegerType->convertToPHPValue('12', $platform)->toDbValue());
        self::assertSame(ParameterType::STRING, $bigIntegerType->getBindingType());
        self::assertSame('BIGINT', $bigIntegerType->getSQLDeclaration([], $platform));

        $longTextType = LongTextValueType::createForValue(TestLongTextValue::class);
        self::assertEquals(new TestLongTextValue('long value'), $longTextType->convertToPHPValue('long value', $platform));
        self::assertSame('CLOB', $longTextType->getSQLDeclaration([], $platform));

        $nativeJsonType = NativeJsonValueType::createForValue(TestNativeJsonValue::class);
        $nativeJsonValue = new TestNativeJsonValue(['key' => 'value']);
        self::assertSame('{"key":"value"}', $nativeJsonType->convertToDatabaseValue($nativeJsonValue, $platform));
        self::assertEquals($nativeJsonValue, $nativeJsonType->convertToPHPValue('{"key":"value"}', $platform));
        self::assertSame('CLOB', $nativeJsonType->getSQLDeclaration([], $platform));

        $serializableJsonType = JsonSerializableValueType::createForValue(TestJsonSerializableValue::class);
        $serializableJsonValue = new TestJsonSerializableValue('value');
        self::assertSame(
            '{"key":"value"}',
            $serializableJsonType->convertToDatabaseValue($serializableJsonValue, $platform),
        );
        self::assertEquals(
            $serializableJsonValue,
            $serializableJsonType->convertToPHPValue('{"key":"value"}', $platform),
        );
    }

    public function testIncompatibleValueClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        BooleanValueType::createForValue(FirstName::class);
    }

    public function testDateConversions(): void
    {
        $platform = new SQLitePlatform();
        $type = DateValueType::createForValue(TestDateValue::class);

        self::assertSame(
            '2026-08-22',
            $type->convertToDatabaseValue(new TestDateValue(new DateTimeImmutable('2026-08-22 12:34:56')), $platform),
        );
        self::assertEquals(
            new TestDateValue(new DateTimeImmutable('2026-08-22 00:00:00')),
            $type->convertToPHPValue('2026-08-22', $platform),
        );
        self::assertSame('DATE', $type->getSQLDeclaration([], $platform));
    }

    public function testDateTimeConversions(): void
    {
        $platform = new SQLitePlatform();
        $type = DateTimeValueType::createForValue(TestDateTimeValue::class);
        $dateTime = new DateTimeImmutable('2026-08-22 12:34:56');

        self::assertSame(
            '2026-08-22 12:34:56',
            $type->convertToDatabaseValue(new TestDateTimeValue($dateTime), $platform),
        );
        self::assertEquals(
            new TestDateTimeValue($dateTime),
            $type->convertToPHPValue('2026-08-22 12:34:56', $platform),
        );
        self::assertSame('DATETIME', $type->getSQLDeclaration([], $platform));
    }

    public function testInvalidValueFormatConversionException(): void
    {
        $exception = new InvalidValueFormatException('invalid', FirstName::class, 'non-empty string');
        $conversionException = $exception->toConversionException();

        self::assertInstanceOf(InvalidFormat::class, $conversionException);
        self::assertSame($exception, $conversionException->getPrevious());
    }
}
