<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidValueException;
use PixelFederation\DoctrineGenericTypesBundle\Value\BaseValue;

/**
 * @template V of BaseValue
 * @psalm-consistent-constructor
 */
abstract class BaseGenericType extends Type implements GenericType
{
    // phpcs:ignore SlevomatCodingStandard.TypeHints.UselessConstantTypeHint.UselessDocComment
    /**
     * @var class-string<V>
     * @psalm-suppress InvalidConstantAssignmentValue
     */
    protected const string ABSTRACT_VALUE = BaseValue::class;

    /**
     * @var class-string<V>
     */
    protected string $class;

    #[Override]
    public static function createForValue(string $class): Type
    {
        if (static::class === self::class) {
            throw new InvalidArgumentException(sprintf(
                'You must set const ABSTRACT_VALUE at %s.',
                static::class,
            ));
        }

        if (!is_a($class, static::ABSTRACT_VALUE, true)) {
            throw new InvalidArgumentException(sprintf(
                'Doctrine Type %s must handle class %s. Got %s',
                static::class,
                static::ABSTRACT_VALUE,
                $class,
            ));
        }
        /**
         * @phpstan-ignore function.alreadyNarrowedType
         */
        assert(is_subclass_of($class, BaseValue::class));

        $self = new static();
        $self->class = $class;

        return $self;
    }

    #[Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        $class = $this->class;
        if (!$value instanceof $class) {
            throw ConversionException::conversionFailedInvalidType(
                $value,
                $this->getName(),
                ['null', $class],
            );
        }

        return $value->toDbValue();
    }

    #[Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        $class = $this->class;
        try {
            return $class::fromDbValue($value);
        } catch (InvalidValueException $e) {
            throw $e->toConversionException();
        }
    }

    #[Override]
    public function getName(): string
    {
        return $this->class;
    }
}
