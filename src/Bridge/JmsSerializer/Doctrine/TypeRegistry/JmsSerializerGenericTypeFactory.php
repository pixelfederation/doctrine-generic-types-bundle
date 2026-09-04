<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Bridge\JmsSerializer\Doctrine\TypeRegistry;

use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use JMS\Serializer\SerializerInterface;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\JmsSerializer\Doctrine\Type\JmsSerializerValueType;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\JmsSerializer\Value\JmsSerializerValue;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\GenericType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\GenericTypeFactory;
use PixelFederation\DoctrineGenericTypesBundle\Value\Value;

final readonly class JmsSerializerGenericTypeFactory implements GenericTypeFactory
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {
    }

    /** @param class-string<GenericType> $type */
    #[Override]
    public function supports(string $type): bool
    {
        return $type === JmsSerializerValueType::class;
    }

    /**
     * @param class-string<GenericType> $type
     * @param class-string<Value<mixed>> $value
     */
    #[Override]
    public function create(string $type, string $value): Type
    {
        $this->assertValueClass($value);

        return new JmsSerializerValueType($value, $this->serializer);
    }

    /**
     * @phpstan-assert class-string<JmsSerializerValue> $value
     * @psalm-assert class-string<JmsSerializerValue> $value
     */
    private function assertValueClass(string $value): void
    {
        if (!is_a($value, JmsSerializerValue::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'Doctrine Type %s must handle class %s. Got %s',
                JmsSerializerValueType::class,
                JmsSerializerValue::class,
                $value,
            ));
        }
    }
}
