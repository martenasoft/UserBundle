<?php

namespace MartenaSoft\UserBundle\DependencyInjection;

use MartenaSoft\CommonLibrary\Dictionary\DictionarySite;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use MartenaSoft\CommonLibrary\Dictionary\DictionaryUser;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('user');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->arrayPrototype()
            ->children()
                ->arrayNode('site')
                    ->isRequired()
                    ->children()
                        ->integerNode('id')
                            ->defaultValue(DictionarySite::DEFAULT_SITE_ID)
                            ->cannotBeEmpty()
                        ->end()

                        ->arrayNode('redirect_to_after_login')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('default_route')
                                    ->defaultValue(DictionaryUser::REDIRECT_TO_AFTER_LOGIN)
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()

                    ->arrayNode('rules')
                        ->arrayPrototype()
                            ->children()
                                ->arrayNode('roles')
                                    ->scalarPrototype()->end()
                                        ->isRequired()
                                        ->requiresAtLeastOneElement()
                                    ->end()

                                    ->scalarNode('route')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
