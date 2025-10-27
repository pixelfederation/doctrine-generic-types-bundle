<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Exception;

use Doctrine\DBAL\Types\ConversionException;
use InvalidArgumentException;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidValueException;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\SuccessRate;
use Throwable;

final class InvalidSuccessRateException extends InvalidArgumentException implements InvalidValueException
{
    public function __construct(
        public readonly float $value,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function notInRage(float $value): self
    {
        return new self(
            $value,
            sprintf(
                '%s must be between 0 and 100, "%.2f" given.',
                SuccessRate::class,
                $value,
            ),
        );
    }

    public function toConversionException(): ConversionException
    {
        return new ConversionException(
            sprintf(
                'Could not convert database value "%.2f" to Doctrine Type %s. %s',
                $this->value,
                SuccessRate::class,
                $this->message,
            ),
            0,
            $this,
        );
    }
}
