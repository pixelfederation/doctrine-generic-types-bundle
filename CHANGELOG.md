# Changelog

All notable changes to this project are documented in this file.

## [2.0.0]

### Requirements

- PHP 8.5 or newer (previously PHP 8.3).
- Doctrine DBAL 4.4 or newer and DoctrineBundle 3.3 or newer.
- Symfony 7.4 or 8.1.
- `ramsey/uuid` is now an optional dependency required only by the Ramsey UUID bridge.
- Symfony YAML is no longer installed by the bundle; applications may still use YAML configuration when they install
  the component themselves.

### Breaking changes

- `StringValue`, `BooleanValue`, `IntegerValue`, `FloatValue`, and Ramsey UUID's `UuidValue` are now abstract
  readonly classes. Every class extending one of them must also be declared `readonly`.
- Replaced `BaseGenericType::ABSTRACT_VALUE` with the abstract `getAbstractValueClass()` method. Custom Doctrine
  types extending `BaseGenericType` must implement the new method instead of overriding the constant.
- Custom Doctrine types extending `BaseGenericType` must implement `createDoctrineType()` and return the native DBAL
  type responsible for SQL declarations, database conversions, and parameter binding.
- Removed `BaseGenericType::getName()` in line with Doctrine DBAL 4, which no longer uses type names from type
  instances.
- Removed the bundle's `Doctrine\Connection\ConnectionFactory` decorator. Generic types are now registered when the
  bundle boots, without calling DoctrineBundle's internal connection factory API.
- String-backed generic fields must define their Doctrine column length explicitly, for example
  `#[ORM\Column(type: FirstName::class, length: 255)]`. Doctrine ORM does not apply its built-in string default to a
  custom type.
- Updated conversion exceptions to the Doctrine DBAL 4 exception classes such as `InvalidType`, `InvalidFormat`,
  `SerializationFailed`, and `ValueNotConvertible`.
- `GenericType` is now a marker interface. Custom types using the static `createForValue()` construction mechanism
  must implement `StaticGenericType` instead.
- Boolean conversion now uses DBAL 4's `convertBooleansToDatabaseValue()` API, and binding types return
  `Doctrine\DBAL\ParameterType`.

### Added

- Added value bases and matching Doctrine types for `BIGINT`, exact `DECIMAL`, `TEXT`/CLOB, ASCII strings, immutable
  `TIME`, and `DATEINTERVAL` mappings: `BigIntegerValue`, `DecimalValue`, `LongTextValue`, `AsciiStringValue`,
  `TimeValue`, and `DateIntervalValue`. Big integers accept only integers or signed decimal integer strings.
- Added immutable `DateValue` and `DateTimeValue` mappings for native Doctrine `DATE` and `DATETIME` values.
- Added two JSON mapping strategies. `JsonSerializableValue` and `JsonSerializableValueType` provide explicit
  serialization through `JsonSerializable` and require concrete values to implement hydration through
  `fromDbValue()`. `NativeJsonValue` and `NativeJsonValueType` delegate native PHP value serialization and
  deserialization to Doctrine DBAL's JSON type.
- Added the extensible, prioritized `GenericTypeFactory` mechanism. Tagged factories can construct Doctrine types
  with injected services; `StaticGenericTypeFactory` handles types implementing `StaticGenericType`.
- Added optional Symfony Serializer and JMS Serializer bridges. Each bridge stores complete value objects as JSON
  and restores their concrete classes through an explicitly configured serializer service. Enabling a bridge
  requires its serializer service ID under `serializer_bridges`.
- Added an optional Symfony UID bridge for UUID and ULID values. Native strategies use GUID/16-byte binary storage;
  `UuidValueChar36Type` and `UlidValueChar26Type` provide canonical fixed-length string storage.
- Added the MIT license file.

### Changed

- Generic types are registered from `PixelFederationDoctrineGenericTypesBundle::boot()`.
- `GenericTypesRegistrator` is now stateless, idempotent, and readonly. It checks the Doctrine type registry on every
  registration call instead of keeping a process-local `registered` flag, making it safe for long-running runtimes.
- The bundle now fails with a descriptive `LogicException` if it is booted without a container or if the registrator
  service has an invalid type.
- The list command initializes Doctrine without executing a dummy SQL query and uses the public
  `Type::getTypesMap()` API instead of the internal `TypeRegistry::getMap()` API.
- Bundle configuration loading, service definitions, test application configuration, and README examples use PHP
  configuration instead of YAML.
- Reworked generic type discovery, mapping, and configuration validation.
- `BaseGenericType` now delegates SQL declarations, database conversions, and parameter binding to a native Doctrine
  DBAL type. The built-in scalar, date, datetime, and Ramsey UUID generic types use the corresponding DBAL types.

### Removed

- Removed `src/Resources/config/services.yaml` and the YAML files from the test application.

### Upgrade guide

1. Upgrade the application to PHP 8.5, Doctrine DBAL 4.4 or newer, DoctrineBundle 3.3 or newer, and Symfony 7.4 or
   8.1.
2. Add `readonly` to every application value class extending one of the bundle's base value classes.
3. For every custom type extending `BaseGenericType`, replace the `ABSTRACT_VALUE` constant with:

   ```php
   /**
    * @return class-string<YourAbstractValue>
    */
   #[Override]
   protected static function getAbstractValueClass(): string
   {
       return YourAbstractValue::class;
   }

   #[Override]
   protected static function createDoctrineType(): Type
   {
       return new StringType();
   }
   ```

   Custom Doctrine types that directly implemented `GenericType` and expose `createForValue()` must implement
   `StaticGenericType` instead.

4. Remove custom uses of Doctrine DBAL 3 APIs such as `Type::getName()`, the old `ConversionException` factories,
   and integer binding-type return values.
5. Add an explicit `length` to every string-backed generic Doctrine column.
6. If the application uses the Ramsey UUID bridge, require `ramsey/uuid` explicitly.
7. YAML application configuration remains possible when the application installs Symfony YAML, but PHP configuration
   is now the documented format and the bundle no longer installs Symfony YAML itself.

[2.0.0]: https://github.com/pixelfederation/doctrine-generic-types-bundle/compare/1.0.0...2.0.0
