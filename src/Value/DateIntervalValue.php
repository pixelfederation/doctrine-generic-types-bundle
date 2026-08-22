<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Value;

use DateInterval;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidDatabaseTypeException;

/**
 * @implements BaseValue<DateInterval>
 * @psalm-consistent-constructor
 */
abstract readonly class DateIntervalValue implements BaseValue
{
    public function __construct(
        protected DateInterval $value,
    ) {
    }

    #[Override]
    public static function fromDbValue(mixed $dbValue): static
    {
        if (!$dbValue instanceof DateInterval) {
            throw new InvalidDatabaseTypeException($dbValue, static::class, [DateInterval::class]);
        }

        return new static($dbValue);
    }

    #[Override]
    public function toDbValue(): DateInterval
    {
        return $this->value;
    }
}
