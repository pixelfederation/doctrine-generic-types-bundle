<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Doctrine\TypeRegistry;

use Doctrine\DBAL\Types\ArrayType;
use Doctrine\DBAL\Types\AsciiStringType;
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\BinaryType;
use Doctrine\DBAL\Types\BlobType;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\DateImmutableType;
use Doctrine\DBAL\Types\DateIntervalType;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateTimeTzImmutableType;
use Doctrine\DBAL\Types\DateTimeTzType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\DecimalType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\GuidType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\ObjectType;
use Doctrine\DBAL\Types\SimpleArrayType;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Types\TimeImmutableType;
use Doctrine\DBAL\Types\TimeType;
use Doctrine\DBAL\Types\TypeRegistry;
use Doctrine\DBAL\Types\Types;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\TypeRegistryProviderInterface;

/**
 * Creates a new isolated instance of {@see TypeRegistry} with all
 * built-in Doctrine DBAL types pre-registered.
 *
 * Reason:
 * {@see \Doctrine\DBAL\Types\Type::getTypeRegistry()} uses an internal
 * static type registry (a singleton) that stores type instances based
 * on their {@see spl_object_id()} value.
 *
 * When PHPUnit runs with `backupStaticProperties="true"`, static
 * properties are serialized and restored between tests. This causes
 * object identities to change and `spl_object_id()` values to be
 * recycled. As a result, {@see TypeRegistry::findTypeName()} may start
 * returning incorrect or conflicting type names (for example, a custom
 * type might receive the same object ID as the built-in "bigint" type).
 *
 * This custom constructor builds a fresh {@see TypeRegistry} with new
 * instances of all built-in types, bypassing Doctrine’s static global
 * registry entirely and preventing `spl_object_id()` collisions during
 * test execution.
 */
final class TypeRegistryProvider implements TypeRegistryProviderInterface
{
    private ?TypeRegistry $typeRegistry = null;

    #[Override]
    public function provide(): TypeRegistry
    {
        if ($this->typeRegistry !== null) {
            return $this->typeRegistry;
        }

        $buildInTypes = [
            Types::ARRAY => ArrayType::class,
            Types::ASCII_STRING => AsciiStringType::class,
            Types::BIGINT => BigIntType::class,
            Types::BINARY => BinaryType::class,
            Types::BLOB => BlobType::class,
            Types::BOOLEAN => BooleanType::class,
            Types::DATE_MUTABLE => DateType::class,
            Types::DATE_IMMUTABLE => DateImmutableType::class,
            Types::DATEINTERVAL => DateIntervalType::class,
            Types::DATETIME_MUTABLE => DateTimeType::class,
            Types::DATETIME_IMMUTABLE => DateTimeImmutableType::class,
            Types::DATETIMETZ_MUTABLE => DateTimeTzType::class,
            Types::DATETIMETZ_IMMUTABLE => DateTimeTzImmutableType::class,
            Types::DECIMAL => DecimalType::class,
            Types::FLOAT => FloatType::class,
            Types::GUID => GuidType::class,
            Types::INTEGER => IntegerType::class,
            Types::JSON => JsonType::class,
            Types::OBJECT => ObjectType::class,
            Types::SIMPLE_ARRAY => SimpleArrayType::class,
            Types::SMALLINT => SmallIntType::class,
            Types::STRING => StringType::class,
            Types::TEXT => TextType::class,
            Types::TIME_MUTABLE => TimeType::class,
            Types::TIME_IMMUTABLE => TimeImmutableType::class,
        ];

        $instances = [];
        foreach ($buildInTypes as $name => $class) {
            $instances[$name] = new $class();
        }

        $this->typeRegistry = new TypeRegistry($instances);

        return $this->typeRegistry;
    }
}
