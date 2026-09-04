<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Value;

use PixelFederation\DoctrineGenericTypesBundle\Bridge\JmsSerializer\Value\JmsSerializerValue;

final readonly class TestJmsSerializerValue implements JmsSerializerValue
{
    public function __construct(
        public string $name,
        public int $count,
    ) {
    }
}
