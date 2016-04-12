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
                    ->scalarNode('url')->isRequired()->end()
                    ->scalarNode('version')->defaultValue('1.1')->end()
                    ->scalarNode('client_id')->defaultNull()->end()
                    ->scalarNode('shared_secret')->defaultNull()->end()
                ->end();

        return $treeBuilder;
    }
}
