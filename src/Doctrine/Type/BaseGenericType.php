<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type;

use Doctrine\DBAL\ParameterType;
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
abstract class BaseGenericType extends Type implements StaticGenericType
{
    /**
     * @var class-string<V>
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected string $class;

    private Type $type;

    public function __construct()
    {
        $this->type = static::createDoctrineType();
    }

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

    /**
     * @inheritDoc
     */
    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $this->type->getSQLDeclaration($column, $platform);
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

        return $this->type->convertToDatabaseValue($value->toDbValue(), $platform);
    }

    #[Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        $dbValue = $this->type->convertToPHPValue($value, $platform);
        $class = $this->class;
        try {
            return $class::fromDbValue($dbValue);
        } catch (InvalidValueException $e) {
            throw $e->toConversionException();
        }
    }

    #[Override]
    public function getBindingType(): ParameterType
    {
        return $this->type->getBindingType();
    }

    /**
     * @return class-string<V>
     */
    abstract protected static function getAbstractValueClass(): string;

    abstract protected static function createDoctrineType(): Type;
}
