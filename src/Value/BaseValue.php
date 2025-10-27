<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Value;

use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidValueException;

/**
 * @template T
 * @extends Value<T>
 */
interface BaseValue extends Value
{
    /**
     * @throws InvalidValueException
     */
    public static function fromDbValue(mixed $dbValue): static;

    public function toDbValue(): mixed;
}
