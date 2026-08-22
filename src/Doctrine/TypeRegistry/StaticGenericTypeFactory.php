<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry;

use Doctrine\DBAL\Types\Type;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\GenericType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\StaticGenericType;
use PixelFederation\DoctrineGenericTypesBundle\Value\Value;

final readonly class StaticGenericTypeFactory implements GenericTypeFactory
{
    /**
     * @param class-string<GenericType> $type
     */
    #[Override]
    public function supports(string $type): bool
    {
        return is_subclass_of($type, StaticGenericType::class);
    }

    /**
     * @param class-string<GenericType> $type
     * @param class-string<Value<mixed>> $value
     */
    #[Override]
    public function create(string $type, string $value): Type
    {
        assert(is_subclass_of($type, StaticGenericType::class));

        return $type::createForValue($value);
    }
}
