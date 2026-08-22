[![Grumphp](https://github.com/pixelfederation/doctrine-generic-types-bundle/actions/workflows/grumphp.yaml/badge.svg)](https://github.com/pixelfederation/doctrine-generic-types-bundle/actions/workflows/grumphp.yaml)
[![Latest Version](https://img.shields.io/packagist/v/pixelfederation/doctrine-generic-types-bundle.svg)](https://packagist.org/packages/pixelfederation/doctrine-generic-types-bundle)
[![Downloads](https://img.shields.io/packagist/dm/pixelfederation/doctrine-generic-types-bundle)](https://packagist.org/packages/pixelfederation/doctrine-generic-types-bundle)

[//]: # ([![Code Coverage]&#40;https://codecov.io/gh/pixelfederation/doctrine-generic-types-bundle/branch/master/graph/badge.svg?token=77JIFYSUC5&#41;]&#40;https://codecov.io/gh/pixelfederation/doctrine-generic-types-bundle&#41;)

# PixelFederation DoctrineGenericTypesBundle

## Installation

Install via Composer:

```bash
composer require pixelfederation/doctrine-generic-types-bundle
```

Register the bundle in `config/bundles.php` if you don't use Symfony Flex:

```php
return [
    // ...
    PixelFederation\DoctrineGenericTypesBundle\PixelFederationDoctrineGenericTypesBundle::class => ['all' => true],
];
```

Bundle configuration:

```php
<?php

// config/packages/pixel_federation_doctrine_generic_types.php

declare(strict_types=1);

use PixelFederation\DoctrineGenericTypesBundle\Bridge\RamseyUuid\Doctrine\Type\UuidValueType;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\RamseyUuid\Value\UuidValue;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Doctrine\Type\UuidValueType as SymfonyUuidValueType;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Value\UuidValue as SymfonyUuidValue;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\BooleanValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\DateTimeValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\DateValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\FloatValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\IntegerValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\StringValueType;
use PixelFederation\DoctrineGenericTypesBundle\Value\BooleanValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\DateTimeValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\DateValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\FloatValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\IntegerValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\StringValue;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('pixel_federation_doctrine_generic_types', [
        'generic_types' => [
            BooleanValue::class => BooleanValueType::class,
            DateTimeValue::class => DateTimeValueType::class,
            DateValue::class => DateValueType::class,
            FloatValue::class => FloatValueType::class,
            IntegerValue::class => IntegerValueType::class,
            StringValue::class => StringValueType::class,
            // Ramsey UUID integration (requires ramsey/uuid)
            UuidValue::class => UuidValueType::class,
            // Symfony UID integration (requires symfony/uid and symfony/doctrine-bridge)
            SymfonyUuidValue::class => SymfonyUuidValueType::class,
        ],
        'directories' => [
            './src/App/Value',
            './src/App/OtherValue',
        ],
    ]);
};
```

## Usage

Create your Value Object:

```php
<?php

declare(strict_types=1);

namespace App\Value;

use PixelFederation\DoctrineGenericTypesBundle\Value\StringValue;

final readonly class FirstName extends StringValue
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
        #[ORM\Column(type: FirstName::class, length: 255)]
        public FirstName $firstName,
    ) {
    }
}
```

Doctrine will handle persisting and retrieving your Value Object automatically.

String-based generic types require an explicit column length because Doctrine ORM only provides the default
length for its built-in `string` type.

## Symfony UID bridge

The optional Symfony UID bridge provides UUID and ULID value bases with two storage strategies each:

- `UuidValueType` delegates to Symfony's Doctrine UUID type. It uses a native GUID where supported and a fixed
  16-byte binary column otherwise.
- `UuidValueChar36Type` stores the canonical RFC 4122 UUID with hyphens in a fixed `CHAR(36)` column.
- `UlidValueType` delegates to Symfony's Doctrine ULID type and uses a native GUID or fixed 16-byte binary column.
- `UlidValueChar26Type` stores the canonical Base32 ULID in a fixed `CHAR(26)` column.

Choose one type for your Symfony UUID value hierarchy in the bundle configuration:

```php
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Doctrine\Type\UuidValueChar36Type;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Doctrine\Type\UuidValueType;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Value\UuidValue;

'generic_types' => [
    UuidValue::class => UuidValueType::class,
    // Or use UuidValueChar36Type::class for CHAR(36) storage.
],
```

## How to create custom Generic Types

Create an abstract value class implementing `PixelFederation\DoctrineGenericTypesBundle\Value\Value` or `PixelFederation\DoctrineGenericTypesBundle\Value\BaseValue`:

```php
<?php

declare(strict_types=1);

namespace App\CustomValue;

use PixelFederation\DoctrineGenericTypesBundle\Value\Value;

abstract readonly class MoneyValue implements Value
{
    public function __construct(
        public float $value,
        public string $currency,
    ) {
        // value object validation logic
    }
}
```

Create a Doctrine type class implementing `PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\GenericType`:

```php
<?php

declare(strict_types=1);

namespace App\Doctrine\Type;

use App\CustomValue\MoneyValue;
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

final class MoneyValueType extends JsonType implements GenericType
{
    /**
     * @var class-string<MoneyValue>
     */
    protected string $class;

    #[Override]
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
            return json_encode(['value' => $value->value, 'currency' => $value->currency], JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw SerializationFailed::new($value, 'json', $e->getMessage(), $e);
        }
    }

    /**
     * @return MoneyValue|null
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

        try {
            $data = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw ValueNotConvertible::new($value, $this->class, $e->getMessage(), $e);
        }

        if (!is_array($data)) {
            throw InvalidFormat::new(
                $value,
                $this->class,
                '{"value": float, "currency": string}',
            );
        }

        $dataValue = $data['value'] ?? null;
        $dataCurrency = $data['currency'] ?? null;
        if (!is_float($dataValue) || !is_string($dataCurrency)) {
            throw InvalidFormat::new(
                $value,
                $this->class,
                '{"value": float, "currency": string}',
            );
        }

        return new ($this->class)($dataValue, $dataCurrency);
    }
}
```

Register your custom Generic Type in the bundle configuration:

```php
<?php

// config/packages/pixel_federation_doctrine_generic_types.php

declare(strict_types=1);

use App\CustomValue\MoneyValue;
use App\Doctrine\Type\MoneyValueType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('pixel_federation_doctrine_generic_types', [
        'generic_types' => [
            // ...
            MoneyValue::class => MoneyValueType::class,
        ],
        'directories' => [
            // ...
            './src/App/CustomValue',
        ],
    ]);
};
```
