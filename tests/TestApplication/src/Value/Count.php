<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value;

use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Exception\InvalidCountException;
use PixelFederation\DoctrineGenericTypesBundle\Value\IntegerValue;

final class Count extends IntegerValue
{
    public function __construct(
        int $value,
    ) {
        if ($value < 0) {
            throw InvalidCountException::lessThanZero($value);
        }

        parent::__construct($value);
    }
}
