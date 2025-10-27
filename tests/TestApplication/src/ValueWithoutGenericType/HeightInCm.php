<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\ValueWithoutGenericType;

use Override;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidDatabaseTypeException;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Exception\InvalidHeightInCmException;
use PixelFederation\DoctrineGenericTypesBundle\Value\BaseValue;

/**
 * @implements BaseValue<int>
 */
final class HeightInCm implements BaseValue
{
    public function __construct(
        public readonly int $value,
    ) {
        if ($value < 0) {
            throw InvalidHeightInCmException::lessThanZero($value);
        }
    }

    #[Override]
    public static function fromDbValue(mixed $dbValue): static
    {
        if (!is_int($dbValue)) {
            throw new InvalidDatabaseTypeException(
                $dbValue,
                self::class,
                ['int'],
            );
        }

        return new self($dbValue);
    }

    #[Override]
    public function toDbValue(): int
    {
        return $this->value;
    }
}
