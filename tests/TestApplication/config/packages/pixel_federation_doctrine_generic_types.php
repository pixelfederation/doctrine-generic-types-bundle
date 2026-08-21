<?php

declare(strict_types=1);

use PixelFederation\DoctrineGenericTypesBundle\Bridge\RamseyUuid\Doctrine\Type\UuidValueType;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\RamseyUuid\Value\UuidValue;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\BooleanValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\FloatValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\IntegerValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\StringValueType;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\CustomValue\MoneyValue;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Doctrine\Type\MoneyValueType;
use PixelFederation\DoctrineGenericTypesBundle\Value\BooleanValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\FloatValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\IntegerValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\StringValue;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('pixel_federation_doctrine_generic_types', [
        'generic_types' => [
            BooleanValue::class => BooleanValueType::class,
            FloatValue::class => FloatValueType::class,
            IntegerValue::class => IntegerValueType::class,
            StringValue::class => StringValueType::class,
            UuidValue::class => UuidValueType::class,
            MoneyValue::class => MoneyValueType::class,
        ],
        'directories' => [
            './tests/TestApplication/src/Value',
            './tests/TestApplication/src/OtherValue',
            './tests/TestApplication/src/CustomValue',
        ],
    ]);
};
