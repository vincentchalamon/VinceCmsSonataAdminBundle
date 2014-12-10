<?php

/*
 * This file is part of the VinceCmsSonataAdmin bundle.
 *
 * (c) Vincent Chalamon <http://www.vincent-chalamon.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Vince\Bundle\CmsSonataAdminBundle\DependencyInjection;

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
        $treeBuilder->root('vince_cms_sonata_admin')
            ->children()
                ->scalarNode('article')
                    ->defaultValue('Vince\Bundle\CmsSonataAdminBundle\Admin\Entity\ArticleAdmin')
                ->end()
                ->scalarNode('menu')
                    ->defaultValue('Vince\Bundle\CmsSonataAdminBundle\Admin\Entity\MenuAdmin')
                ->end()
                ->scalarNode('block')
                    ->defaultValue('Vince\Bundle\CmsSonataAdminBundle\Admin\Entity\BlockAdmin')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
