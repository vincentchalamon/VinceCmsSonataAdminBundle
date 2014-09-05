<?php

/*
 * This file is part of the VinceCmsSonataAdmin bundle.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Vince\Bundle\CmsSonataAdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class VinceCmsSonataAdminExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('vince.admin.article.class', $config['article']);
        $container->setParameter('vince.admin.block.class', $config['block']);
        $container->setParameter('vince.admin.menu.class', $config['menu']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        // Configure Assetic if AsseticBundle is activated
        if (isset($bundles['AsseticBundle']) && $container->hasExtension('assetic')) {
            $container->prependExtensionConfig('assetic', array('bundles' => array('VinceCmsSonataAdminBundle')));
        }

        // Configure SonataDoctrineORMAdmin if SonataDoctrineORMAdminBundle is activated
        if (isset($bundles['SonataDoctrineORMAdminBundle']) && $container->hasExtension('sonata_doctrine_orm_admin')) {
            $container->prependExtensionConfig('sonata_doctrine_orm_admin', array(
                    'templates' => array(
                        'types' => array(
                            'list' => array(
                                'localizeddate'   => 'VinceCmsSonataAdminBundle:List:localizeddate.html.twig',
                                'array'           => 'VinceCmsSonataAdminBundle:List:array.html.twig',
                                'url'             => 'VinceCmsSonataAdminBundle:List:url.html.twig',
                                'html'            => 'VinceCmsSonataAdminBundle:List:html.html.twig',
                                'field_tree_up'   => 'VinceCmsSonataAdminBundle:List:field_tree_up.html.twig',
                                'field_tree_down' => 'VinceCmsSonataAdminBundle:List:field_tree_down.html.twig'
                            ),
                            'show' => array(
                                'localizeddate'   => 'VinceCmsSonataAdminBundle:Show:localizeddate.html.twig',
                                'array'           => 'VinceCmsSonataAdminBundle:Show:array.html.twig',
                                'url'             => 'VinceCmsSonataAdminBundle:Show:url.html.twig',
                                'html'            => 'VinceCmsSonataAdminBundle:Show:html.html.twig'
                            )
                        )
                    )
                )
            );
        }

        // Configure SonataAdmin if SonataAdminBundle is activated
        if (isset($bundles['SonataAdminBundle']) && $container->hasExtension('sonata_admin')) {
            $container->prependExtensionConfig('sonata_admin', array(
                    'templates' => array(
                        'layout' => 'VinceCmsSonataAdminBundle::standard_layout.html.twig'
                    ),
                    'options' => array(
                        'confirm_exit' => false
                    ),
                    'title' => 'Vince CMS'
                )
            );
        }
    }
}
