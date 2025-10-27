<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Value;

use Override;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidDatabaseTypeException;

/**
 * @implements BaseValue<float>
 * @psalm-consistent-constructor
 */
abstract class FloatValue implements BaseValue
{
    public function __construct(
        protected float $value,
    ) {
    }

    #[Override]
    public static function fromDbValue(mixed $dbValue): static
    {
        if (!is_float($dbValue)) {
            throw new InvalidDatabaseTypeException(
                $dbValue,
                static::class,
                ['float'],
            );
        }

        return new static($dbValue);
    }

    #[Override]
    public function toDbValue(): float
    {
        return $this->value;
    }
}
