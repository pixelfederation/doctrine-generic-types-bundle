<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Doctrine\TypeRegistry;

use InvalidArgumentException;
use JMS\Serializer\SerializerBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\JmsSerializer\Doctrine\Type\JmsSerializerValueType;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\JmsSerializer\Doctrine\TypeRegistry\JmsSerializerGenericTypeFactory;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonySerializer\Doctrine\Type\SymfonySerializerValueType;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonySerializer\Doctrine\TypeRegistry\SymfonySerializerGenericTypeFactory;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\StringValueType;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\FirstName;
use PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Value\TestJmsSerializerValue;
use PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Value\TestSymfonySerializerValue;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(JmsSerializerGenericTypeFactory::class)]
#[CoversClass(SymfonySerializerGenericTypeFactory::class)]
final class SerializerGenericTypeFactoryTest extends TestCase
{
    public function testSymfonyFactory(): void
    {
        $factory = new SymfonySerializerGenericTypeFactory(
            new Serializer([new ObjectNormalizer()], [new JsonEncoder()]),
        );

        self::assertTrue($factory->supports(SymfonySerializerValueType::class));
        self::assertFalse($factory->supports(StringValueType::class));
        self::assertInstanceOf(
            SymfonySerializerValueType::class,
            $factory->create(SymfonySerializerValueType::class, TestSymfonySerializerValue::class),
        );
    }

    public function testJmsFactory(): void
    {
        $factory = new JmsSerializerGenericTypeFactory(SerializerBuilder::create()->build());

        self::assertTrue($factory->supports(JmsSerializerValueType::class));
        self::assertFalse($factory->supports(StringValueType::class));
        self::assertInstanceOf(
            JmsSerializerValueType::class,
            $factory->create(JmsSerializerValueType::class, TestJmsSerializerValue::class),
        );
    }

    public function testFactoryRejectsIncompatibleValue(): void
    {
        $factory = new SymfonySerializerGenericTypeFactory(
            new Serializer([new ObjectNormalizer()], [new JsonEncoder()]),
        );

        $this->expectException(InvalidArgumentException::class);

        $factory->create(SymfonySerializerValueType::class, FirstName::class);
    }

    public function testJmsFactoryRejectsIncompatibleValue(): void
    {
        $factory = new JmsSerializerGenericTypeFactory(SerializerBuilder::create()->build());

        $this->expectException(InvalidArgumentException::class);

        $factory->create(JmsSerializerValueType::class, FirstName::class);
    }
}
