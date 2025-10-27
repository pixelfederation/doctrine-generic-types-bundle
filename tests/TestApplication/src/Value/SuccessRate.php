<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value;

use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Exception\InvalidSuccessRateException;
use PixelFederation\DoctrineGenericTypesBundle\Value\FloatValue;

final class SuccessRate extends FloatValue
{
    public function __construct(
        float $value,
    ) {
        if ($value < 0.0 || $value > 100.0) {
            throw InvalidSuccessRateException::notInRage($value);
        }

        parent::__construct($value);
    }
}
