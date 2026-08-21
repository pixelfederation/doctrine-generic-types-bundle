<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\CustomValue;

use PixelFederation\DoctrineGenericTypesBundle\Value\Value;

abstract readonly class MoneyValue implements Value
{
    public function __construct(
        public float $value,
        public Currency $currency,
    ) {
    }
}
