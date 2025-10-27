<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Exception;

use Doctrine\DBAL\Types\ConversionException;
use InvalidArgumentException;
use Override;

final class InvalidDatabaseTypeException extends InvalidArgumentException implements InvalidValueException
{
    /**
     * @param array<string> $expectedTypes
     */
    public function __construct(
        public mixed $dbValue,
        public string $typeName,
        public array $expectedTypes,
    ) {
        parent::__construct(sprintf(
            'Invalid database value for type %s: %s. Expected types: %s',
            $typeName,
            get_debug_type($dbValue),
            implode(', ', $expectedTypes),
        ));
    }

    #[Override]
    public function toConversionException(): ConversionException
    {
        return ConversionException::conversionFailedInvalidType(
            $this->dbValue,
            $this->typeName,
            $this->expectedTypes,
            $this,
        );
    }
}
