<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Exception;

use Doctrine\DBAL\Types\ConversionException;
use InvalidArgumentException;
use Override;
use Throwable;

final class InvalidValueFormatException extends InvalidArgumentException implements InvalidValueException
{
    public function __construct(
        public string $value,
        public string $toType,
        public string $expectedFormat,
        ?Throwable $previous = null,
    ) {
        $value = strlen($value) > 32 ? substr($value, 0, 20) . '...' : $value;

        parent::__construct(
            sprintf(
                'Invalid input for class %s: %s. Expected format: %s',
                $toType,
                $value,
                $expectedFormat,
            ),
            0,
            $previous,
        );
    }

    #[Override]
    public function toConversionException(): ConversionException
    {
        return ConversionException::conversionFailedFormat(
            $this->value,
            $this->toType,
            $this->expectedFormat,
            $this,
        );
    }
}
