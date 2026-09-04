<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\Persistence\ManagerRegistry;
use PixelFederation\DoctrineGenericTypesBundle\Command\ListCommand;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\DefaultTypeRegistryProvider;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\GenericTypeFactoryProvider;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\GenericTypesRegistrator;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\StaticGenericTypeFactory;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\TypeRegistryProvider;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(
        'pixel_federation.doctrine_generic_types.default_type_registry_provider',
        DefaultTypeRegistryProvider::class,
    );

    $services->alias(
        TypeRegistryProvider::class,
        'pixel_federation.doctrine_generic_types.default_type_registry_provider',
    );

    $services->set(
        'pixel_federation.doctrine_generic_types.static_generic_type_factory',
        StaticGenericTypeFactory::class,
    )
        ->tag(GenericTypeFactoryProvider::TAG, ['priority' => GenericTypeFactoryProvider::DEFAULT_PRIORITY]);

    $services->set(
        'pixel_federation.doctrine_generic_types.generic_type_factory_provider',
        GenericTypeFactoryProvider::class,
    )
        ->arg(
            '$factories',
            tagged_iterator(GenericTypeFactoryProvider::TAG),
        );

    $services->set(
        'pixel_federation.doctrine_generic_types.generic_types_registrator',
        GenericTypesRegistrator::class,
    )
        ->public()
        ->arg('$typeRegistryProvider', service(TypeRegistryProvider::class))
        ->arg(
            '$genericTypeFactoryProvider',
            service('pixel_federation.doctrine_generic_types.generic_type_factory_provider'),
        )
        ->arg('$genericTypesMapping', param('pixel_federation.doctrine_generic_types.generic_types_mapping'));

    $services->set(
        'pixel_federation.doctrine_generic_types.command.list',
        ListCommand::class,
    )
        ->arg('$registry', service(ManagerRegistry::class))
        ->tag('console.command');
};
