<?php

namespace ConnectHolland\TulipAPIBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration validates and merges configuration from the app/config files.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tulip_api');

        $rootNode
            ->children()
                ->scalarNode('url')
                    ->isRequired()
                ->end()
                ->enumNode('version')
                    ->values(array('1.1'))
                    ->defaultValue('1.1')
                ->end()
                ->scalarNode('client_id')
                    ->defaultNull()
                ->end()
                ->scalarNode('shared_secret')
                    ->defaultNull()
                ->end()
                ->scalarNode('file_upload_path')
                    ->defaultNull()
                ->end()
                ->arrayNode('objects')
                    ->useAttributeAsKey('name')
                    ->isRequired()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('service')
                                ->isRequired()
                            ->end()
                            ->scalarNode('action')
                                ->defaultValue('save')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
