<?php

namespace Skimia\AngularBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class GlobalConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('skimia_angular');
        $rootNode
            ->children()
                ->scalarNode('app_name')
                    ->defaultValue('SkimiaAngularApp')
                ->end()
                ->arrayNode('dependencies')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')                          
                            ->end()
                            ->scalarNode('resource')                          
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('bundles')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('resource')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                ->end()
             ->end();
                

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
