[![Grumphp](https://github.com/pixelfederation/doctrine-generic-types-bundle/actions/workflows/grumphp.yaml/badge.svg)](https://github.com/pixelfederation/doctrine-generic-types-bundle/actions/workflows/grumphp.yaml)
[![Latest Version](https://img.shields.io/packagist/v/pixelfederation/doctrine-generic-types-bundle.svg)](https://packagist.org/packages/pixelfederation/doctrine-generic-types-bundle)
[![Downloads](https://img.shields.io/packagist/dm/pixelfederation/doctrine-generic-types-bundley)](https://packagist.org/packages/pixelfederation/doctrine-generic-types-bundle)

[//]: # ([![Code Coverage]&#40;https://codecov.io/gh/pixelfederation/doctrine-generic-types-bundle/branch/master/graph/badge.svg?token=77JIFYSUC5&#41;]&#40;https://codecov.io/gh/pixelfederation/doctrine-generic-types-bundle&#41;)

# PixelFederation DoctrineGenericTypesBundle

## Installation

install via Composer:

```bash
composer require pixelfederation/doctrine-generic-types-bundle
```

register bundle in `config/bundles.php` (if you don't use Symfony Flex)

```php
return [
    // ...
    PixelFederation\DoctrineGenericTypesBundle\PixelFederationDoctrineGenericTypesBundle::class => ['all' => true],
];
```

bundle configuration:

```yaml
# config/packages/pixel_federation_doctrine_generic_types.yaml
pixel_federation_doctrine_generic_types:
    generic_types:
        PixelFederation\DoctrineGenericTypesBundle\Value\BooleanValue: PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\BooleanValueType
        PixelFederation\DoctrineGenericTypesBundle\Value\FloatValue: PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\FloatValueType
        PixelFederation\DoctrineGenericTypesBundle\Value\IntegerValue: PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\IntegerValueType
        PixelFederation\DoctrineGenericTypesBundle\Value\StringValue: PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\StringValueType
        # https://github.com/ramsey/uuid integration
        PixelFederation\DoctrineGenericTypesBundle\Bridge\RamseyUuid\Value\UuidValue: PixelFederation\DoctrineGenericTypesBundle\Bridge\RamseyUuid\Doctrine\Type\UuidValueType
    # Directories where to find your Value Objects
    directories:
        - ./src/App/Value
        - ./src/App/OtherValue
```

## Usage

Crete your Value Object:

```php
<?php

declare(strict_types=1);

namespace App\Value;

use PixelFederation\DoctrineGenericTypesBundle\Value\StringValue;

final class FirstName extends StringValue
{
}
```

Use it in your Doctrine entity:

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Value\FirstName;

#[ORM\Entity]
#[ORM\Table(name: 'person')]
class Person
{
    public function __construct(
        #[ORM\Column(type: FirstName::class)]
        public FirstName $firstName,
    ) {
    }
}
```

doctrine will handle persisting and retrieving your Value Object automatically.

## How to create custom Generic Types

Create abstract Value class extending `PixelFederation\DoctrineGenericTypesBundle\Value\Value` or `PixelFederation\DoctrineGenericTypesBundle\Value\BaseValue`:

```php
<?php

declare(strict_types=1);

namespace App\CustomValue;

use PixelFederation\DoctrineGenericTypesBundle\Value\Value;

abstract class MoneyValue implements Value
{
    public function __construct(
        public readonly float $value,
        public readonly string $currency,
    ) {
        // value object validation logic
    }
}
```

Create Doctrine Type class extending `PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\GenericType`:

```php
<?php

declare(strict_types=1);

namespace App\Doctrine\Type;

use App\CustomValue\MoneyValue;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use JsonException;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\GenericType;

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
            return json_encode(['value' => $value->value, 'currency' => $value->currency], JSON_THROW_ON_ERROR);
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
        $dataCurrency = $data['currency'] ?? null;
        if (!is_float($dataValue) || !is_string($dataCurrency)) {
            throw ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                '{"value": float, "currency": string}',
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
```

Register your custom Generic Type in the bundle configuration:

```yaml
# config/packages/pixel_federation_doctrine_generic_types.yaml
pixel_federation_doctrine_generic_types:
    generic_types:
        # ...
        App\CustomValue\MoneyValue: App\Doctrine\Type\MoneyValueType
    directories:
        # ...
        - ./src/App/CustomValue
```
