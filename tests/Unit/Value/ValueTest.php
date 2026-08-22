<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Value;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\RamseyUuid\Value\UuidValue;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidDatabaseTypeException;
use PixelFederation\DoctrineGenericTypesBundle\Value\BaseValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\BooleanValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\DateTimeValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\DateValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\FloatValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\IntegerValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\StringValue;
use Ramsey\Uuid\Uuid;

#[CoversClass(BooleanValue::class)]
#[CoversClass(DateTimeValue::class)]
#[CoversClass(DateValue::class)]
#[CoversClass(FloatValue::class)]
#[CoversClass(IntegerValue::class)]
#[CoversClass(StringValue::class)]
#[CoversClass(UuidValue::class)]
final class ValueTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string<BaseValue<mixed>>, mixed}>
     */
    public static function scalarValueProvider(): iterable
    {
        yield 'boolean' => [TestBooleanValue::class, true];
        yield 'float' => [TestFloatValue::class, 12.5];
        yield 'integer' => [TestIntegerValue::class, 12];
        yield 'string' => [TestStringValue::class, 'value'];
    }

    /**
     * @return iterable<string, array{class-string<BaseValue<mixed>>, mixed}>
     */
    public static function invalidScalarValueProvider(): iterable
    {
        yield 'boolean' => [TestBooleanValue::class, 1];
        yield 'float' => [TestFloatValue::class, 1];
        yield 'integer' => [TestIntegerValue::class, '1'];
        yield 'string' => [TestStringValue::class, 1];
    }

    /**
     * @param class-string<BaseValue<mixed>> $valueClass
     */
    #[DataProvider('scalarValueProvider')]
    public function testScalarValueRoundTrip(string $valueClass, mixed $dbValue): void
    {
        $value = $valueClass::fromDbValue($dbValue);

        self::assertInstanceOf($valueClass, $value);
        self::assertSame($dbValue, $value->toDbValue());
    }

    /**
     * @param class-string<BaseValue<mixed>> $valueClass
     */
    #[DataProvider('invalidScalarValueProvider')]
    public function testScalarValueRejectsInvalidDatabaseType(string $valueClass, mixed $dbValue): void
    {
        $this->expectException(InvalidDatabaseTypeException::class);

        $valueClass::fromDbValue($dbValue);
    }

    public function testUuidValueRoundTrip(): void
    {
        $uuid = Uuid::fromString('018f47a2-4c0b-7d16-a8d4-9a2dcfd82c19');
        $value = new TestUuidValue($uuid);

        self::assertSame($uuid->toString(), $value->toDbValue());
        self::assertSame($uuid->toString(), (string) $value);
        self::assertEquals($value, TestUuidValue::fromDbValue($uuid->toString()));
    }

    public function testUuidValueRejectsInvalidDatabaseType(): void
    {
        $this->expectException(InvalidDatabaseTypeException::class);

        TestUuidValue::fromDbValue(1);
    }

    public function testDateValuesRoundTrip(): void
    {
        $date = new DateTimeImmutable('2026-08-22 12:34:56');

        self::assertSame($date, TestDateValue::fromDbValue($date)->toDbValue());
        self::assertSame($date, TestDateTimeValue::fromDbValue($date)->toDbValue());
    }

    public function testDateValueRejectsInvalidDatabaseType(): void
    {
        $this->expectException(InvalidDatabaseTypeException::class);

        TestDateValue::fromDbValue('2026-08-22');
    }

    public function testDateTimeValueRejectsInvalidDatabaseType(): void
    {
        $this->expectException(InvalidDatabaseTypeException::class);

        TestDateTimeValue::fromDbValue('2026-08-22 12:34:56');
    }
}
