<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Bridge\JmsSerializer\Doctrine\Type;

use JMS\Serializer\SerializerInterface;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\BaseSerializerValueType;
use PixelFederation\DoctrineGenericTypesBundle\Value\Value;

final class JmsSerializerValueType extends BaseSerializerValueType
{
    /**
     * @param class-string<Value<string>> $valueClass
     */
    public function __construct(
        string $valueClass,
        private readonly SerializerInterface $serializer,
    ) {
        parent::__construct($valueClass);
    }

    #[Override]
    protected function serialize(object $value): string
    {
        return $this->serializer->serialize($value, 'json');
    }

    #[Override]
    protected function deserialize(string $value, string $valueClass): mixed
    {
        return $this->serializer->deserialize($value, $valueClass, 'json');
    }
}
