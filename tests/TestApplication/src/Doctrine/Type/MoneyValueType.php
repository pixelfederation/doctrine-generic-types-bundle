<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\SerializationFailed;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
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
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
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

        try {
            return json_encode(['value' => $value->value, 'currency' => $value->currency->name], JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw SerializationFailed::new($value, 'json', $e->getMessage(), $e);
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
            throw InvalidType::new($value, $this->class, ['null', 'string']);
        }

        $data = $this->decode($value);

        $dataValue = $data['value'] ?? null;
        $dataCurrency = Currency::tryFrom($data['currency'] ?? null);
        if (!is_float($dataValue) || $dataCurrency === null) {
            throw InvalidFormat::new(
                $value,
                $this->class,
                '{"value": float, "currency": enumString}',
            );
        }

        return new ($this->class)($dataValue, $dataCurrency);
    }

    /**
     * @return array<array-key, mixed>
     */
    private function decode(string $value): array
    {
        try {
            $data = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw ValueNotConvertible::new($value, $this->class, $e->getMessage(), $e);
        }

        if (!is_array($data)) {
            throw InvalidFormat::new(
                $value,
                $this->class,
                '{"value": float, "currency": enumString}',
            );
        }

        return $data;
    }
}
