<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\SerializationFailed;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\JsonType;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Value\Value;
use Throwable;

abstract class BaseSerializerValueType extends JsonType implements GenericType
{
    /**
     * @param class-string<Value<string>> $valueClass
     */
    public function __construct(
        protected readonly string $valueClass,
    ) {
    }

    #[Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        $valueClass = $this->valueClass;
        if (!$value instanceof $valueClass) {
            throw InvalidType::new($value, $valueClass, ['null', $valueClass]);
        }

        try {
            return $this->serialize($value);
        } catch (Throwable $exception) {
            throw SerializationFailed::new($value, 'json', $exception->getMessage(), $exception);
        }
    }

    #[Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }
        if (!is_string($value)) {
            throw InvalidType::new($value, $this->valueClass, ['null', 'string']);
        }

        try {
            $result = $this->deserialize($value, $this->valueClass);
        } catch (Throwable $exception) {
            throw ValueNotConvertible::new($value, $this->valueClass, $exception->getMessage(), $exception);
        }

        $valueClass = $this->valueClass;
        if (!$result instanceof $valueClass) {
            throw InvalidType::new($result, $valueClass, [$valueClass]);
        }

        return $result;
    }

    abstract protected function serialize(object $value): string;

    /**
     * @param class-string $valueClass
     */
    abstract protected function deserialize(string $value, string $valueClass): mixed;
}
