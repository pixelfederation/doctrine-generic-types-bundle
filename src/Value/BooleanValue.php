<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Value;

use Override;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidDatabaseTypeException;

/**
 * @implements BaseValue<bool>
 * @psalm-consistent-constructor
 */
abstract class BooleanValue implements BaseValue
{
    public function __construct(
        protected bool $value,
    ) {
    }

    #[Override]
    public static function fromDbValue(mixed $dbValue): static
    {
        if (!is_bool($dbValue)) {
            throw new InvalidDatabaseTypeException(
                $dbValue,
                static::class,
                ['boolean'],
            );
        }

        return new static($dbValue);
    }

    #[Override]
    public function toDbValue(): bool
    {
        return $this->value;
    }
}
