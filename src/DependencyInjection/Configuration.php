<?php

namespace MartenaSoft\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('user');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('redirect_to_after_login')
                    ->isRequired()
                    ->children()

                        ->scalarNode('default_route')
                            ->info('Default route after login if no rule matches')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()

                        ->arrayNode('rules')
                            ->arrayPrototype()
                                ->children()
                                    ->arrayNode('roles')
                                        ->scalarPrototype()->end()
                                        ->requiresAtLeastOneElement()
                                    ->end()

                                    ->scalarNode('route')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()

                                    ->integerNode('site_id')
                                        ->defaultValue(0)
                                    ->end()
                                ->end()
                            ->end()
                        ->end()

                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
