<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Exception;

use Doctrine\DBAL\Types\ConversionException;
use InvalidArgumentException;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Exception\InvalidValueException;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\ValueWithoutGenericType\HeightInCm;
use Throwable;

final class InvalidHeightInCmException extends InvalidArgumentException implements InvalidValueException
{
    public function __construct(
        public readonly int $value,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function lessThanZero(int $value): self
    {
        return new self(
            $value,
            sprintf(
                '%s must be greater than 0, "%d" given.',
                HeightInCm::class,
                $value,
            ),
        );
    }

    #[Override]
    public function toConversionException(): ConversionException
    {
        return new ConversionException(
            sprintf(
                'Could not convert database value "%d" to Doctrine Type %s. %s',
                $this->value,
                HeightInCm::class,
                $this->message,
            ),
            0,
            $this,
        );
    }
}
