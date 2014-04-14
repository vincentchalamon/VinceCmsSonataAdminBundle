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

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Vince\Bundle\CmsBundle\Entity\Block;

/**
 * Bock admin
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class BlockAdmin extends Admin
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
     * {@inheritdoc}
     */
    public function getBatchActions()
    {
        return array_merge(parent::getBatchActions(), array(
                'publish' => array(
                    'label'            => $this->trans('action.publish', array(), 'SonataAdminBundle'),
                    'ask_confirmation' => true
                ),
                'unpublish' => array(
                    'label'            => $this->trans('action.unpublish', array(), 'SonataAdminBundle'),
                    'ask_confirmation' => true
                )
            )
        );
    }

    /**
     * Publish element
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param Block $object
     */
    public function publish(Block $object)
    {
        $object->publish();
    }

    /**
     * Unpublish element
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param Block $object
     */
    public function unpublish(Block $object)
    {
        $object->unpublish();
    }

    /**
     * Configure routes
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list', 'edit'));
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
            ->addIdentifier('title', null, array(
                    'label' => 'block.field.title'
                )
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
            ->add('title', null, array(
                    'label' => 'block.field.title'
                )
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $mapper)
    {
        $mapper
            ->with('block.field.contents')
                ->add('contents', 'redactor', array(
                        'label' => false
                    )
                )
            ->end();
    }
}