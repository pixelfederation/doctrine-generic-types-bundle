<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Value;

use Override;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidDatabaseTypeException;
use PixelFederation\DoctrineGenericTypesBundle\Value\BaseValue;
use Stringable;
use Symfony\Component\Uid\Ulid;

/**
 * require https://github.com/symfony/uid
 *
 * @implements BaseValue<Ulid>
 * @psalm-consistent-constructor
 */
abstract readonly class UlidValue implements BaseValue, Stringable
{
    public function __construct(
        protected Ulid $value,
    ) {
    }

    #[Override]
    public static function fromDbValue(mixed $dbValue): static
    {
        if (!$dbValue instanceof Ulid) {
            throw new InvalidDatabaseTypeException(
                $dbValue,
                static::class,
                [Ulid::class],
            );
        }

        return new static($dbValue);
    }

    #[Override]
    public function toDbValue(): Ulid
    {
        return $this->value;
    }

    /**
     * If you want to use this field as an entity identifier, Doctrine must be able to cast it to a string.
     * {@see \Doctrine\ORM\UnitOfWork::getIdHashByIdentifier()}
     */
    #[Override]
    public function __toString(): string
    {
        return $this->value->toBase32();
    }
}
