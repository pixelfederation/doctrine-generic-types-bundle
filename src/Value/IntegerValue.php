<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Value;

use Override;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidDatabaseTypeException;

/**
 * @implements BaseValue<int>
 * @psalm-consistent-constructor
 */
abstract class IntegerValue implements BaseValue
{
    public function __construct(
        protected int $value,
    ) {
    }

    #[Override]
    public static function fromDbValue(mixed $dbValue): static
    {
        if (!is_int($dbValue)) {
            throw new InvalidDatabaseTypeException(
                $dbValue,
                static::class,
                ['int'],
            );
        }

        return new static($dbValue);
    }

    #[Override]
    public function toDbValue(): int
    {
        return $this->value;
    }
}
