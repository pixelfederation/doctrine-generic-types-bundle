<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\CustomValue;

enum Currency: string
{
    case EUR = 'EUR';
    case USD = 'USD';
}
