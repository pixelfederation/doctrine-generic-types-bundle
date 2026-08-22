<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Type;
use Override;
use Symfony\Component\Uid\Uuid;

final class UuidChar36Type extends Type
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL([
            ...$column,
            'fixed' => true,
            'length' => 36,
        ]);
    }

    #[Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof Uuid) {
            throw InvalidType::new($value, self::class, ['null', Uuid::class]);
        }

        return $value->toRfc4122();
    }

    #[Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Uuid
    {
        if ($value === null || $value instanceof Uuid) {
            return $value;
        }

        if (!is_string($value)) {
            throw InvalidType::new($value, self::class, ['null', 'string', Uuid::class]);
        }

        if (!Uuid::isValid($value, Uuid::FORMAT_RFC_4122)) {
            throw InvalidFormat::new($value, self::class, 'RFC 4122 UUID');
        }

        return Uuid::fromString($value);
    }
}
