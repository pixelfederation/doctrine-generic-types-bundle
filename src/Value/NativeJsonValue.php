<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Value;

use Override;

/**
 * @implements BaseValue<mixed>
 * @psalm-consistent-constructor
 */
abstract readonly class NativeJsonValue implements BaseValue
{
    public function __construct(
        protected mixed $value,
    ) {
    }

    #[Override]
    public static function fromDbValue(mixed $dbValue): static
    {
        return new static($dbValue);
    }

    #[Override]
    public function toDbValue(): mixed
    {
        return $this->value;
    }
}
