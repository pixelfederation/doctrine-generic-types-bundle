<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Value;

use Override;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidDatabaseTypeException;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidValueFormatException;

/**
 * @implements BaseValue<string>
 * @psalm-consistent-constructor
 */
abstract readonly class AsciiStringValue implements BaseValue
{
    public function __construct(
        protected string $value,
    ) {
        if (preg_match('/[^\x00-\x7F]/', $value) === 1) {
            throw new InvalidValueFormatException($value, static::class, 'ASCII string');
        }
    }

    #[Override]
    public static function fromDbValue(mixed $dbValue): static
    {
        if (!is_string($dbValue)) {
            throw new InvalidDatabaseTypeException($dbValue, static::class, ['string']);
        }

        return new static($dbValue);
    }

    #[Override]
    public function toDbValue(): string
    {
        return $this->value;
    }
}
