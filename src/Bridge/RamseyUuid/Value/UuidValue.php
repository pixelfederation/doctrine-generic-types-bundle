<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Bridge\RamseyUuid\Value;

use Override;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidDatabaseTypeException;
use PixelFederation\DoctrineGenericTypesBundle\Value\BaseValue;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Stringable;

/**
 * require https://github.com/ramsey/uuid
 *
 * @implements BaseValue<UuidInterface>
 * @psalm-consistent-constructor
 */
abstract class UuidValue implements BaseValue, Stringable
{
    public function __construct(
        protected UuidInterface $value,
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

        return new static(Uuid::fromString($dbValue));
    }

    #[Override]
    public function toDbValue(): string
    {
        return $this->value->toString();
    }

    /**
     * If you want to use this field as an entity identifier, Doctrine must be able to cast it to a string.
     * {@see \Doctrine\ORM\UnitOfWork::getIdHashByIdentifier()}
     */
    #[Override]
    public function __toString(): string
    {
        return $this->value->toString();
    }
}
