<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Doctrine\Type;

use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\SerializationFailed;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use JMS\Serializer\SerializerBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\JmsSerializer\Doctrine\Type\JmsSerializerValueType;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonySerializer\Doctrine\Type\SymfonySerializerValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\BaseSerializerValueType;
use PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Value\TestJmsSerializerValue;
use PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Value\TestSymfonySerializerValue;
use RuntimeException;
use stdClass;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(BaseSerializerValueType::class)]
#[CoversClass(JmsSerializerValueType::class)]
#[CoversClass(SymfonySerializerValueType::class)]
final class SerializerValueTypeTest extends TestCase
{
    public function testSymfonySerializerRoundTrip(): void
    {
        $type = new SymfonySerializerValueType(
            TestSymfonySerializerValue::class,
            new Serializer([new ObjectNormalizer()], [new JsonEncoder()]),
        );

        $this->assertRoundTrip($type, new TestSymfonySerializerValue('value', 12));
    }

    public function testJmsSerializerRoundTrip(): void
    {
        $type = new JmsSerializerValueType(
            TestJmsSerializerValue::class,
            SerializerBuilder::create()->build(),
        );

        $this->assertRoundTrip($type, new TestJmsSerializerValue('value', 12));
    }

    public function testInvalidJsonIsConvertedToDoctrineException(): void
    {
        $type = new SymfonySerializerValueType(
            TestSymfonySerializerValue::class,
            new Serializer([new ObjectNormalizer()], [new JsonEncoder()]),
        );

        $this->expectException(ValueNotConvertible::class);

        $type->convertToPHPValue('{invalid', new SQLitePlatform());
    }

    public function testRejectsIncompatibleValueObject(): void
    {
        $type = new SymfonySerializerValueType(
            TestSymfonySerializerValue::class,
            $this->createStub(SerializerInterface::class),
        );

        $this->expectException(InvalidType::class);

        $type->convertToDatabaseValue(new stdClass(), new SQLitePlatform());
    }

    public function testSerializationFailureIsConvertedToDoctrineException(): void
    {
        $serializer = $this->createStub(SerializerInterface::class);
        $serializer->method('serialize')->willThrowException(new RuntimeException('Serialization failed.'));
        $type = new SymfonySerializerValueType(TestSymfonySerializerValue::class, $serializer);

        $this->expectException(SerializationFailed::class);

        $type->convertToDatabaseValue(new TestSymfonySerializerValue('value', 12), new SQLitePlatform());
    }

    public function testReadsJsonFromResource(): void
    {
        $type = new SymfonySerializerValueType(
            TestSymfonySerializerValue::class,
            new Serializer([new ObjectNormalizer()], [new JsonEncoder()]),
        );
        $resource = fopen('php://memory', 'r+');
        self::assertIsResource($resource);
        fwrite($resource, '{"name":"value","count":12}');
        rewind($resource);

        self::assertEquals(
            new TestSymfonySerializerValue('value', 12),
            $type->convertToPHPValue($resource, new SQLitePlatform()),
        );

        fclose($resource);
    }

    public function testRejectsInvalidDatabaseValueType(): void
    {
        $type = new SymfonySerializerValueType(
            TestSymfonySerializerValue::class,
            $this->createStub(SerializerInterface::class),
        );

        $this->expectException(InvalidType::class);

        $type->convertToPHPValue(12, new SQLitePlatform());
    }

    public function testRejectsUnexpectedDeserializedObject(): void
    {
        $serializer = $this->createStub(SerializerInterface::class);
        $serializer->method('deserialize')->willReturn(new stdClass());
        $type = new SymfonySerializerValueType(TestSymfonySerializerValue::class, $serializer);

        $this->expectException(InvalidType::class);

        $type->convertToPHPValue('{}', new SQLitePlatform());
    }

    private function assertRoundTrip(BaseSerializerValueType $type, object $value): void
    {
        $platform = new SQLitePlatform();
        $databaseValue = $type->convertToDatabaseValue($value, $platform);

        self::assertSame('{"name":"value","count":12}', $databaseValue);
        self::assertEquals($value, $type->convertToPHPValue($databaseValue, $platform));
        self::assertNull($type->convertToDatabaseValue(null, $platform));
        self::assertNull($type->convertToPHPValue(null, $platform));
        self::assertSame('CLOB', $type->getSQLDeclaration([], $platform));
    }
}
