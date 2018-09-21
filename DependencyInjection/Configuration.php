<?php

namespace Puzzle\Admin\NewsletterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('puzzle_admin_newsletter');
        
        $rootNode
            ->children()
                ->scalarNode('title')->defaultValue('newsletter.title')->end()
                ->scalarNode('description')->defaultValue('newsletter.description')->end()
                ->scalarNode('icon')->defaultValue('newsletter.icon')->end()
                ->arrayNode('roles')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('newsletter')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('label')->defaultValue('ROLE_NEWSLETTER')->end()
                                ->scalarNode('description')->defaultValue('newsletter.role.default')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('dirname')->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
