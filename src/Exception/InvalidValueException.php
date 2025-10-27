<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Exception;

use Doctrine\DBAL\Types\ConversionException;
use Throwable;

interface InvalidValueException extends Throwable
{
    public function toConversionException(): ConversionException;
}
