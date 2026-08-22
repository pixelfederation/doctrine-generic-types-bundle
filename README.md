[![Grumphp](https://github.com/pixelfederation/doctrine-generic-types-bundle/actions/workflows/grumphp.yaml/badge.svg)](https://github.com/pixelfederation/doctrine-generic-types-bundle/actions/workflows/grumphp.yaml)
[![Latest Version](https://img.shields.io/packagist/v/pixelfederation/doctrine-generic-types-bundle.svg)](https://packagist.org/packages/pixelfederation/doctrine-generic-types-bundle)
[![Downloads](https://img.shields.io/packagist/dm/pixelfederation/doctrine-generic-types-bundle)](https://packagist.org/packages/pixelfederation/doctrine-generic-types-bundle)
[![Code Coverage](https://codecov.io/gh/pixelfederation/doctrine-generic-types-bundle/branch/main/graph/badge.svg)](https://codecov.io/gh/pixelfederation/doctrine-generic-types-bundle)

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

Configure the value hierarchy, its Doctrine type, and the directory containing concrete values:

```php
<?php

// config/packages/pixel_federation_doctrine_generic_types.php

declare(strict_types=1);

use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\StringValueType;
use PixelFederation\DoctrineGenericTypesBundle\Value\StringValue;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('pixel_federation_doctrine_generic_types', [
        'generic_types' => [
            StringValue::class => StringValueType::class,
        ],
        'directories' => [
            './src/Value',
        ],
    ]);
};
```

## Usage

Create a value object:

```php
<?php

declare(strict_types=1);

namespace App\Value;

use PixelFederation\DoctrineGenericTypesBundle\Value\StringValue;

final readonly class FirstName extends StringValue
{
}
```

Use its concrete class as the Doctrine column type:

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

Doctrine now persists and hydrates `FirstName` automatically. Add mappings only for the value families used by the
application; the complete list is below.

String-based generic types require an explicit column length because Doctrine ORM only provides the default
length for its built-in `string` type.

## Built-in value types

The bundle provides these base value classes and matching Doctrine types:

| Value base              | Doctrine type               | PHP database value          | Column options          |
|-------------------------|-----------------------------|-----------------------------|-------------------------|
| `AsciiStringValue`      | `AsciiStringValueType`      | ASCII `string`              | `length`                |
| `BooleanValue`          | `BooleanValueType`          | `bool`                      | —                       |
| `IntegerValue`          | `IntegerValueType`          | `int`                       | —                       |
| `BigIntegerValue`       | `BigIntegerValueType`       | `int` or integer `string`   | —                       |
| `FloatValue`            | `FloatValueType`            | `float`                     | —                       |
| `DecimalValue`          | `DecimalValueType`          | numeric `string`            | `precision` and `scale` |
| `StringValue`           | `StringValueType`           | `string`                    | `length`                |
| `LongTextValue`         | `LongTextValueType`         | `string`                    | —                       |
| `DateValue`             | `DateValueType`             | `DateTimeImmutable`         | —                       |
| `DateTimeValue`         | `DateTimeValueType`         | `DateTimeImmutable`         | —                       |
| `TimeValue`             | `TimeValueType`             | `DateTimeImmutable`         | —                       |
| `DateIntervalValue`     | `DateIntervalValueType`     | `DateInterval`              | —                       |
| `JsonSerializableValue` | `JsonSerializableValueType` | result of `jsonSerialize()` | —                       |
| `NativeJsonValue`       | `NativeJsonValueType`       | `mixed`                     | —                       |

`BigIntegerValue` accepts integers and signed decimal integer strings so that values outside PHP's integer range
remain exact.
`DecimalValue` accepts numeric strings to avoid floating-point precision loss. Decimal entity fields must declare
their precision and scale, for example `#[ORM\Column(type: Price::class, precision: 10, scale: 2)]`.
`AsciiStringValue` rejects non-ASCII characters and, like `StringValue`, requires an explicit column length.
`TimeValue` represents a time of day without a date or timezone; DBAL hydrates its `DateTimeImmutable` value with
`1970-01-01` as the date. `DateIntervalValue` delegates Doctrine's portable ISO-8601 interval representation to the
native DBAL `dateinterval` type.

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
`GenericTypeFactoryProvider::TAG` and selects the first supporting factory in priority order. The built-in
`StaticGenericTypeFactory` remains responsible for types implementing
`StaticGenericType`. Serializer types implement only the `GenericType` marker because their factories construct them
with dependencies from the service container.

Neither bridge is enabled by merely installing its serializer package. A configured bridge fails container
compilation when its service ID does not exist, preventing a persistence mapping from silently using the wrong
serializer.

Serializer output becomes part of the database format. Changes to property names, exclusion rules, groups, custom
normalizers, handlers, or serializer metadata may therefore require a data migration. Keep serializer configuration
used for persistence stable and ensure that it can reconstruct readonly constructors and nested value objects.

## Ramsey UUID bridge

Install Ramsey UUID:

```bash
composer require ramsey/uuid
```

Extend the Ramsey `UuidValue` base and map its hierarchy to the GUID-backed `UuidValueType`:

```php
use PixelFederation\DoctrineGenericTypesBundle\Bridge\RamseyUuid\Doctrine\Type\UuidValueType;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\RamseyUuid\Value\UuidValue;

'generic_types' => [
    UuidValue::class => UuidValueType::class,
],
```

## Symfony UID bridge

The optional Symfony UID bridge provides UUID and ULID value bases with two storage strategies each:

```bash
composer require symfony/uid symfony/doctrine-bridge
```

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

## Custom generic types

For values backed by a native DBAL type, extend `BaseGenericType`. It supplies the common validation and conversion
flow; the custom type only selects its value hierarchy and underlying DBAL type:

```php
use App\Value\EmailAddress;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\BaseGenericType;

/** @extends BaseGenericType<EmailAddress> */
final class EmailAddressType extends BaseGenericType
{
    #[Override]
    protected static function getAbstractValueClass(): string
    {
        return EmailAddress::class;
    }

    #[Override]
    protected static function createDoctrineType(): Type
    {
        return new StringType();
    }
}
```

Map `EmailAddress::class` to `EmailAddressType::class` under `generic_types` and include the directory containing its
concrete subclasses. Implement `StaticGenericType` directly only when `BaseGenericType` cannot model the required
conversion.

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
