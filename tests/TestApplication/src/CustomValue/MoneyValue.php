<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\CustomValue;

use PixelFederation\DoctrineGenericTypesBundle\Value\Value;

abstract class MoneyValue implements Value
{
    public function __construct(
        public readonly float $value,
        public readonly Currency $currency,
    ) {
    }
}
