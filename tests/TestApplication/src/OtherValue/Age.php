<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\OtherValue;

use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Exception\InvalidAgeException;
use PixelFederation\DoctrineGenericTypesBundle\Value\IntegerValue;

final class Age extends IntegerValue
{
    public function __construct(
        int $value,
    ) {
        if ($value < 0) {
            throw InvalidAgeException::lessThanZero($value);
        }

        parent::__construct($value);
    }
}
