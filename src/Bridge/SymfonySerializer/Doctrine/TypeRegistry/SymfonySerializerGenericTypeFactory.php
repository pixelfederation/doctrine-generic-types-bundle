<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonySerializer\Doctrine\TypeRegistry;

use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonySerializer\Doctrine\Type\SymfonySerializerValueType;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonySerializer\Value\SymfonySerializerValue;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\GenericType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\GenericTypeFactory;
use PixelFederation\DoctrineGenericTypesBundle\Value\Value;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class SymfonySerializerGenericTypeFactory implements GenericTypeFactory
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {
    }

    /** @param class-string<GenericType> $type */
    #[Override]
    public function supports(string $type): bool
    {
        return $type === SymfonySerializerValueType::class;
    }

    /**
     * @param class-string<GenericType> $type
     * @param class-string<Value<mixed>> $value
     */
    #[Override]
    public function create(string $type, string $value): Type
    {
        $this->assertValueClass($value);

        return new SymfonySerializerValueType($value, $this->serializer);
    }

    /**
     * @phpstan-assert class-string<SymfonySerializerValue> $value
     * @psalm-assert class-string<SymfonySerializerValue> $value
     */
    private function assertValueClass(string $value): void
    {
        if (!is_a($value, SymfonySerializerValue::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'Doctrine Type %s must handle class %s. Got %s',
                SymfonySerializerValueType::class,
                SymfonySerializerValue::class,
                $value,
            ));
        }
    }
}
