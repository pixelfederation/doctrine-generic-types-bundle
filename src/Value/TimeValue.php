<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Value;

use DateTimeImmutable;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidDatabaseTypeException;

/**
 * @implements BaseValue<DateTimeImmutable>
 * @psalm-consistent-constructor
 */
abstract readonly class TimeValue implements BaseValue
{
    public function __construct(
        protected DateTimeImmutable $value,
    ) {
    }

    #[Override]
    public static function fromDbValue(mixed $dbValue): static
    {
        if (!$dbValue instanceof DateTimeImmutable) {
            throw new InvalidDatabaseTypeException($dbValue, static::class, [DateTimeImmutable::class]);
        }

        return new static($dbValue);
    }

    #[Override]
    public function toDbValue(): DateTimeImmutable
    {
        return $this->value;
    }
}
