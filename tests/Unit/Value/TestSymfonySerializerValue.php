<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Value;

use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonySerializer\Value\SymfonySerializerValue;

final readonly class TestSymfonySerializerValue implements SymfonySerializerValue
{
    public function __construct(
        public string $name,
        public int $count,
    ) {
    }
}
