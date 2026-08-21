# Changelog

All notable changes to this project are documented in this file.

## [2.0.0]

### Requirements

- Raised the minimum PHP version from 8.3 to 8.5.
- Upgraded Doctrine DBAL from 3.x to 4.4 or newer.
- Upgraded DoctrineBundle from 2.16 or newer to 3.3 or newer.
- Upgraded the supported Symfony versions from 7.3 to 7.4 and 8.1.
- Moved Doctrine ORM and Symfony Dotenv to development dependencies because they are only required by the test
  application.
- Removed the direct Symfony ExpressionLanguage dependency; development tooling installs it transitively when needed.
- Removed the direct Symfony YAML dependency. The bundle's internal service configuration and the documented
  application configuration now use PHP.
- Added Symfony Flex as a development dependency and added the generated `symfony.lock`.
- Added `ramsey/uuid` to Composer suggestions. It remains optional and is required only when using the Ramsey UUID
  bridge.

### Breaking changes

- `StringValue`, `BooleanValue`, `IntegerValue`, `FloatValue`, and Ramsey UUID's `UuidValue` are now abstract
  readonly classes. Every class extending one of them must also be declared `readonly`.
- Replaced `BaseGenericType::ABSTRACT_VALUE` with the abstract `getAbstractValueClass()` method. Custom Doctrine
  types extending `BaseGenericType` must implement the new method instead of overriding the constant.
- Removed `BaseGenericType::getName()` in line with Doctrine DBAL 4, which no longer uses type names from type
  instances.
- Removed the bundle's `Doctrine\Connection\ConnectionFactory` decorator. Generic types are now registered when the
  bundle boots, without calling DoctrineBundle's internal connection factory API.
- String-backed generic fields must define their Doctrine column length explicitly, for example
  `#[ORM\Column(type: FirstName::class, length: 255)]`. Doctrine ORM does not apply its built-in string default to a
  custom type.
- Updated conversion exceptions to the Doctrine DBAL 4 exception classes such as `InvalidType`, `InvalidFormat`,
  `SerializationFailed`, and `ValueNotConvertible`.
- Boolean conversion now uses DBAL 4's `convertBooleansToDatabaseValue()` API, and binding types return
  `Doctrine\DBAL\ParameterType`.

### Added

- Added the MIT license file.
- Added a PHP service configuration at `src/Resources/config/services.php` with explicit bundle-prefixed service IDs.
- Added a test verifying that `pxfd:doctrine_generic_types:list` is present in the console command listing together
  with its description.
- Updated Doctrine schema validation coverage for DBAL 4.
- Added CI coverage for Symfony 7.4 and Symfony 8.1 on PHP 8.5.
- Added a lowest-supported-dependencies CI variant using `composer update --prefer-lowest --prefer-stable` with
  Symfony 7.4.
- Added CODEOWNERS and updated the Docker development setup.

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
- Reworked generic type discovery and mapping with named Psalm type aliases and explicit configuration validation.
- Updated the README examples for readonly value objects, DBAL 4 exceptions, explicit string lengths, and PHP
  configuration.
- Updated PHPUnit from 12.4 to 13.3 and adjusted tests for PHPUnit 13 static-state behavior.
- Updated code quality tooling for PHP 8.5 and reduced unnecessary Psalm and PHPCS suppressions.

### Removed

- Removed `src/Resources/config/services.yaml` and the YAML files from the test application.
- Removed the obsolete Doctrine connection factory decorator.
- Removed PHP Mess Detector and its configuration; the remaining checks continue to run through GrumPHP.
- Removed Symfony Test Pack in favor of explicit test dependencies.

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
   ```

4. Remove custom uses of Doctrine DBAL 3 APIs such as `Type::getName()`, the old `ConversionException` factories,
   and integer binding-type return values.
5. Add an explicit `length` to every string-backed generic Doctrine column.
6. If the application uses the Ramsey UUID bridge, require `ramsey/uuid` explicitly.
7. YAML application configuration remains possible when the application installs Symfony YAML, but PHP configuration
   is now the documented format and the bundle no longer installs Symfony YAML itself.

[2.0.0]: https://github.com/pixelfederation/doctrine-generic-types-bundle/compare/1.0.0...2.0.0
