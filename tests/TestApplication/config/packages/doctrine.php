<?php

declare(strict_types=1);

use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Doctrine\Type\HeightInCmType;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\ValueWithoutGenericType\HeightInCm;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('doctrine', [
        'dbal' => [
            'url' => 'sqlite:///%kernel.project_dir%/var/sqlite/app.db',
            'types' => [
                HeightInCm::class => HeightInCmType::class,
            ],
        ],
        'orm' => [
            'auto_mapping' => true,
            'mappings' => [
                'Entity' => [
                    'is_bundle' => false,
                    'type' => 'attribute',
                    'dir' => '%kernel.project_dir%/src/Entity',
                    'prefix' => 'PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Entity',
                    'alias' => 'Entity',
                ],
            ],
        ],
    ]);
};
