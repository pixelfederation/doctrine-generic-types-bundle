<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Value;

use JsonSerializable;
use Override;

/**
 * @implements BaseValue<mixed>
 * @psalm-consistent-constructor
 */
abstract readonly class JsonSerializableValue implements BaseValue, JsonSerializable
{
    #[Override]
    final public function toDbValue(): mixed
    {
        return $this->jsonSerialize();
    }
}
