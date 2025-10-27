<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\DependencyInjection;

use Override;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public const string CONFIGURATION_ROOT_NODE = 'pixel_federation_doctrine_generic_types';

    #[Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::CONFIGURATION_ROOT_NODE);
        $rootNode = $treeBuilder->getRootNode();
        assert($rootNode instanceof ArrayNodeDefinition);

        $rootNode
            ->children()
                ->arrayNode('generic_types')
                    ->useAttributeAsKey('fqcn')
                    ->prototype('scalar');

        $rootNode
            ->children()
                ->arrayNode('directories')
                    ->useAttributeAsKey('path')
                    ->prototype('scalar');

        return $treeBuilder;
    }
}
