<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Value;

use Override;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidDatabaseTypeException;
use PixelFederation\DoctrineGenericTypesBundle\Value\BaseValue;
use Stringable;
use Symfony\Component\Uid\Uuid;

/**
 * require https://github.com/symfony/uid
 *
 * @implements BaseValue<Uuid>
 * @psalm-consistent-constructor
 */
abstract readonly class UuidValue implements BaseValue, Stringable
{
    public function __construct(
        protected Uuid $value,
    ) {
    }

    #[Override]
    public static function fromDbValue(mixed $dbValue): static
    {
        if (!$dbValue instanceof Uuid) {
            throw new InvalidDatabaseTypeException(
                $dbValue,
                static::class,
                [Uuid::class],
            );
        }

        return new static($dbValue);
    }

    #[Override]
    public function toDbValue(): Uuid
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
        return $this->value->toRfc4122();
    }
}
