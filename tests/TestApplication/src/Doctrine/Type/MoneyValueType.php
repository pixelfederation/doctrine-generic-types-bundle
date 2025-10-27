<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use JsonException;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\GenericType;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\CustomValue\Currency;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\CustomValue\MoneyValue;

final class MoneyValueType extends JsonType implements GenericType
{
    /**
     * @var class-string<MoneyValue>
     */
    protected string $class;

    public static function createForValue(string $class): Type
    {
        if (!is_a($class, MoneyValue::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'Doctrine Type %s must handle class %s. Got %s',
                self::class,
                MoneyValue::class,
                $class,
            ));
        }

        $self = new self();
        $self->class = $class;

        return $self;
    }

    #[Override]
    public function getName(): string
    {
        return $this->class;
    }

    #[Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
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

        try {
            return json_encode(['value' => $value->value, 'currency' => $value->currency->name], JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw ConversionException::conversionFailedSerialization($value, 'json', $e->getMessage());
        }
    }

    /**
     * @return object<MoneyValue>|null
     */
    #[Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), 'json');
        }

        try {
            $data = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            assert(is_array($data));
        } catch (JsonException $e) {
            throw ConversionException::conversionFailedUnserialization($value, $e->getMessage());
        }

        $dataValue = $data['value'] ?? null;
        $dataCurrency = Currency::tryFrom($data['currency'] ?? null);
        if (!is_float($dataValue) || $dataCurrency === null) {
            throw ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                '{"value": float, "currency": enumString}',
            );
        }

        return new ($this->class)($dataValue, $dataCurrency);
    }

    #[Override]
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return false;
    }
}
