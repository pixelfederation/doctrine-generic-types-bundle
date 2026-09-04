<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Value;

use Override;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidDatabaseTypeException;
use PixelFederation\DoctrineGenericTypesBundle\Value\JsonSerializableValue;

final readonly class TestJsonSerializableValue extends JsonSerializableValue
{
    public function __construct(
        public string $key,
    ) {
    }

    #[Override]
    public static function fromDbValue(mixed $dbValue): static
    {
        if (!is_array($dbValue) || !isset($dbValue['key']) || !is_string($dbValue['key'])) {
            throw new InvalidDatabaseTypeException($dbValue, static::class, ['array{key: string}']);
        }

        return new static($dbValue['key']);
    }

    /** @return array{key: string} */
    #[Override]
    public function jsonSerialize(): array
    {
        return ['key' => $this->key];
    }
}
