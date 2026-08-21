<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
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
    /**
     * @var class-string<V>
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected string $class;

    #[Override]
    public static function createForValue(string $class): Type
    {
        $abstractValueClass = static::getAbstractValueClass();
        if (!is_a($class, $abstractValueClass, true)) {
            throw new InvalidArgumentException(sprintf(
                'Doctrine Type %s must handle class %s. Got %s',
                static::class,
                $abstractValueClass,
                $class,
            ));
        }
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
            throw InvalidType::new(
                $value,
                $class,
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

    /**
     * @return class-string<V>
     */
    abstract protected static function getAbstractValueClass(): string;
}
