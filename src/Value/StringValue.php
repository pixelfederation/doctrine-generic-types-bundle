<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Value;

use Override;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidDatabaseTypeException;

/**
 * @implements BaseValue<string>
 * @psalm-consistent-constructor
 */
abstract class StringValue implements BaseValue
{
    public function __construct(
        protected string $value,
    ) {
    }

    #[Override]
    public static function fromDbValue(mixed $dbValue): static
    {
        if (!is_string($dbValue)) {
            throw new InvalidDatabaseTypeException(
                $dbValue,
                static::class,
                ['string'],
            );
        }

        return new static($dbValue);
    }

    #[Override]
    public function toDbValue(): string
    {
        return $this->value;
    }
}
