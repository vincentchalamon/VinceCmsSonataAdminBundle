<?php

/*
 * This file is part of the VinceCmsSonataAdmin bundle.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Vince\Bundle\CmsSonataAdminBundle\Admin\Entity;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Bock admin
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class BlockAdmin extends PublishableAdmin
{

    /**
     * {@inheritdoc}
     */
    protected $baseRoutePattern = 'blocs';

    /**
     * {@inheritdoc}
     */
    protected $datagridValues = array(
        '_page'       => 1,
        '_sort_order' => 'ASC',
        '_sort_by'    => 'title'
    );

    /**
     * Configure routes
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list', 'edit', 'batch'));
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper->addIdentifier('title', null, array(
                'label' => 'block.field.title'
            )
        );
        parent::configureListFields($mapper);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $mapper)
    {
        $mapper
            ->with('block.field.contents', array('class' => 'col-md-6'))
                ->add('title', null, array(
                        'label' => 'block.field.title'
                    )
                )
                ->add('contents', 'redactor', array(
                        'label' => 'block.field.contents'
                    )
                )
            ->end();
        parent::configureFormFields($mapper);
    }
}
