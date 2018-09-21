<?php

namespace Puzzle\Admin\NewsletterBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PuzzleAdminNewsletterExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        $container->setParameter('admin_newsletter', $config);
        $container->setParameter('admin_newsletter.title', $config['title']);
        $container->setParameter('admin_newsletter.description', $config['description']);
        $container->setParameter('admin_newsletter.icon', $config['icon']);
        $container->setParameter('admin_newsletter.roles', $config['roles']);
        $container->setParameter('admin_newsletter.dirname', $config['dirname']);
    }
}
