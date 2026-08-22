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
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\BigIntegerValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\BooleanValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\DateTimeValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\DateValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\DecimalValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\FloatValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\IntegerValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\JsonSerializableValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\LongTextValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\NativeJsonValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\StringValueType;
use PixelFederation\DoctrineGenericTypesBundle\Value\BigIntegerValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\BooleanValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\DateTimeValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\DateValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\DecimalValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\FloatValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\IntegerValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\JsonSerializableValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\LongTextValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\NativeJsonValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\StringValue;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('pixel_federation_doctrine_generic_types', [
        'generic_types' => [
            BigIntegerValue::class => BigIntegerValueType::class,
            BooleanValue::class => BooleanValueType::class,
            DateTimeValue::class => DateTimeValueType::class,
            DateValue::class => DateValueType::class,
            DecimalValue::class => DecimalValueType::class,
            FloatValue::class => FloatValueType::class,
            IntegerValue::class => IntegerValueType::class,
            JsonSerializableValue::class => JsonSerializableValueType::class,
            LongTextValue::class => LongTextValueType::class,
            NativeJsonValue::class => NativeJsonValueType::class,
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

## Built-in value types

The bundle provides these base value classes and matching Doctrine types:

| Value base | Doctrine type | PHP database value | Column options |
| --- | --- | --- | --- |
| `BooleanValue` | `BooleanValueType` | `bool` | — |
| `IntegerValue` | `IntegerValueType` | `int` | — |
| `BigIntegerValue` | `BigIntegerValueType` | `int|string` | — |
| `FloatValue` | `FloatValueType` | `float` | — |
| `DecimalValue` | `DecimalValueType` | numeric `string` | `precision` and `scale` |
| `StringValue` | `StringValueType` | `string` | `length` |
| `LongTextValue` | `LongTextValueType` | `string` | — |
| `DateValue` | `DateValueType` | `DateTimeImmutable` | — |
| `DateTimeValue` | `DateTimeValueType` | `DateTimeImmutable` | — |
| `JsonSerializableValue` | `JsonSerializableValueType` | result of `jsonSerialize()` | — |
| `NativeJsonValue` | `NativeJsonValueType` | `mixed` | — |

`BigIntegerValue` accepts integers and numeric strings so that values outside PHP's integer range remain exact.
`DecimalValue` accepts numeric strings to avoid floating-point precision loss. Decimal entity fields must declare
their precision and scale, for example `#[ORM\Column(type: Price::class, precision: 10, scale: 2)]`.

## JSON values

Two JSON value bases are available. `JsonSerializableValue` is intended for structured value objects. It requires
the concrete value to implement `jsonSerialize()` and `fromDbValue()`, making both serialization and hydration
explicit:

```php
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Value\JsonSerializableValue;

final readonly class Address extends JsonSerializableValue
{
    public function __construct(
        public string $city,
        public string $street,
    ) {
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return ['city' => $this->city, 'street' => $this->street];
    }

    #[Override]
    public static function fromDbValue(mixed $dbValue): static
    {
        // Validate the decoded JSON structure as required by the domain.
        return new static($dbValue['city'], $dbValue['street']);
    }
}
```

`NativeJsonValue` stores any value accepted by Doctrine DBAL's JSON type. Doctrine serializes the value with
`json_encode()` and decodes JSON objects into associative arrays when loading them. This is convenient for native
arrays and scalar JSON values, but applications are responsible for ensuring that the value is JSON-serializable.

Configure each hierarchy with its corresponding Doctrine type:

```php
'generic_types' => [
    JsonSerializableValue::class => JsonSerializableValueType::class,
    NativeJsonValue::class => NativeJsonValueType::class,
],
```

## Serializer bridges

Serializer bridges persist the complete value object as JSON and deserialize the stored JSON directly back into
its concrete value class. Unlike `JsonSerializableValue`, the value object does not implement its own serialization
or hydration methods; serializer metadata and configuration control both directions.

### Symfony Serializer

Install and configure Symfony Serializer in the application. The Serializer Pack includes the normalizers and
supporting components commonly needed for object hydration:

```bash
composer require symfony/serializer-pack
```

Implement `SymfonySerializerValue`:

```php
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonySerializer\Value\SymfonySerializerValue;

final readonly class Address implements SymfonySerializerValue
{
    public function __construct(
        public string $city,
        public string $street,
    ) {
    }
}
```

Map the hierarchy to `SymfonySerializerValueType`:

```php
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonySerializer\Doctrine\Type\SymfonySerializerValueType;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonySerializer\Value\SymfonySerializerValue;

'generic_types' => [
    SymfonySerializerValue::class => SymfonySerializerValueType::class,
],
'serializer_bridges' => [
    'symfony' => [
        'service' => 'serializer',
    ],
],
```

The `service` option is required when the Symfony bridge is enabled and may reference the standard `serializer`
service or a dedicated serializer service. The bundle registers `SymfonySerializerGenericTypeFactory` with that
service and creates one Doctrine type instance for each discovered concrete `SymfonySerializerValue` class.

### JMS Serializer

Install and enable JMSSerializerBundle so that the application container exposes the `jms_serializer` service:

```bash
composer require jms/serializer-bundle
```

```php
// config/bundles.php
return [
    // ...
    JMS\SerializerBundle\JMSSerializerBundle::class => ['all' => true],
];
```

Implement `JmsSerializerValue` and add any JMS metadata required by the value object:

```php
use PixelFederation\DoctrineGenericTypesBundle\Bridge\JmsSerializer\Value\JmsSerializerValue;

final readonly class Address implements JmsSerializerValue
{
    public function __construct(
        public string $city,
        public string $street,
    ) {
    }
}
```

Map the hierarchy to `JmsSerializerValueType`:

```php
use PixelFederation\DoctrineGenericTypesBundle\Bridge\JmsSerializer\Doctrine\Type\JmsSerializerValueType;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\JmsSerializer\Value\JmsSerializerValue;

'generic_types' => [
    JmsSerializerValue::class => JmsSerializerValueType::class,
],
'serializer_bridges' => [
    'jms' => [
        'service' => 'jms_serializer',
    ],
],
```

The `service` option is required when the JMS bridge is enabled. It normally references the `jms_serializer` service
provided by JMSSerializerBundle, but it may reference any service implementing JMS `SerializerInterface`.

Both bridges use the generic type factory mechanism. `GenericTypeFactoryProvider` receives all services tagged with
`GenericTypeFactoryProvider::TAG`, checks them in order, and selects the first factory supporting the configured
Doctrine type. The built-in `StaticGenericTypeFactory` remains responsible for types implementing
`StaticGenericType`. Serializer types implement only the `GenericType` marker because their factories construct them
with dependencies from the service container.

Neither bridge is enabled by merely installing its serializer package. A configured bridge fails container
compilation when its service ID does not exist, preventing a persistence mapping from silently using the wrong
serializer.

Serializer output becomes part of the database format. Changes to property names, exclusion rules, groups, custom
normalizers, handlers, or serializer metadata may therefore require a data migration. Keep serializer configuration
used for persistence stable and ensure that it can reconstruct readonly constructors and nested value objects.

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

Create a Doctrine type class implementing
`PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\StaticGenericType`:

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
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\StaticGenericType;

final class MoneyValueType extends JsonType implements StaticGenericType
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

### Custom `GenericTypeFactory`

Types implementing `StaticGenericType` are instantiated by the built-in `StaticGenericTypeFactory` through their
`createForValue()` method. If a Doctrine type needs services from the dependency injection container, implement only
the `GenericType` marker and create it with a custom `GenericTypeFactory` instead.

For example, given a Doctrine type whose constructor requires an application serializer:

```php
namespace App\Doctrine\Type;

use App\Serializer\ValueSerializer;
use Doctrine\DBAL\Types\JsonType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\GenericType;
use PixelFederation\DoctrineGenericTypesBundle\Value\Value;

final class SerializedValueType extends JsonType implements GenericType
{
    /** @param class-string<Value<mixed>> $valueClass */
    public function __construct(
        private readonly string $valueClass,
        private readonly ValueSerializer $serializer,
    ) {
    }

    // Implement the Doctrine conversion methods using $valueClass and $serializer.
}
```

Create a factory that supports that Doctrine type and passes its dependencies to each created instance:

```php
namespace App\Doctrine\TypeRegistry;

use App\Doctrine\Type\SerializedValueType;
use App\Serializer\ValueSerializer;
use Doctrine\DBAL\Types\Type;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\GenericType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\GenericTypeFactory;
use PixelFederation\DoctrineGenericTypesBundle\Value\Value;

final readonly class SerializedValueTypeFactory implements GenericTypeFactory
{
    public function __construct(
        private ValueSerializer $serializer,
    ) {
    }

    /** @param class-string<GenericType> $type */
    #[Override]
    public function supports(string $type): bool
    {
        return $type === SerializedValueType::class;
    }

    /**
     * @param class-string<GenericType> $type
     * @param class-string<Value<mixed>> $value
     */
    #[Override]
    public function create(string $type, string $value): Type
    {
        return new SerializedValueType($value, $this->serializer);
    }
}
```

Register the factory as a service tagged with `GenericTypeFactoryProvider::TAG`:

```php
// config/services.php

use App\Doctrine\TypeRegistry\SerializedValueTypeFactory;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\GenericTypeFactoryProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set(SerializedValueTypeFactory::class)
        ->autowire()
        ->tag(GenericTypeFactoryProvider::TAG, ['priority' => 100]);
};
```

The value hierarchy is mapped to the custom type in the same way as any built-in generic type:

```php
use App\CustomValue\SerializedValue;
use App\Doctrine\Type\SerializedValueType;

'generic_types' => [
    SerializedValue::class => SerializedValueType::class,
],
```

Symfony orders the tagged factories by their `priority` from highest to lowest. The built-in factories use
`GenericTypeFactoryProvider::DEFAULT_PRIORITY` (`0`), so a positive priority allows a custom factory to take
precedence. Factories with the same priority retain their container registration order.

`GenericTypeFactoryProvider` uses the first ordered factory whose `supports()` method returns `true`. A factory should
therefore support only the Doctrine types it can construct and should validate the supplied value class in
`create()` when the type is restricted to a particular value hierarchy.
