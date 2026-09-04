<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry;

use Doctrine\DBAL\Types\Type;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\GenericType;
use PixelFederation\DoctrineGenericTypesBundle\Value\Value;

interface GenericTypeFactory
{
    /**
     * @param class-string<GenericType> $type
     */
    public function supports(string $type): bool;

    /**
     * @param class-string<GenericType> $type
     * @param class-string<Value<mixed>> $value
     */
    public function create(string $type, string $value): Type;
}
