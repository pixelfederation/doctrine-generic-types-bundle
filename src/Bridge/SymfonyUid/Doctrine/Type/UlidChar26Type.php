<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonyUid\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use Override;
use Symfony\Component\Uid\Ulid;

final class UlidChar26Type extends Type
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
            'length' => 26,
        ]);
    }

    #[Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof Ulid) {
            throw InvalidType::new($value, self::class, ['null', Ulid::class]);
        }

        return $value->toBase32();
    }

    #[Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Ulid
    {
        if ($value === null || $value instanceof Ulid) {
            return $value;
        }

        if (!is_string($value)) {
            throw InvalidType::new($value, self::class, ['null', 'string', Ulid::class]);
        }

        try {
            return Ulid::fromBase32($value);
        } catch (InvalidArgumentException $e) {
            throw InvalidFormat::new($value, self::class, '26 Base32 characters', $e);
        }
    }
}
