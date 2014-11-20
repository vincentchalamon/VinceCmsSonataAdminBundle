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

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Admin\Admin;

/**
 * Publishable admin
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
abstract class PublishableAdmin extends Admin
{

    /**
     * {@inheritdoc}
     */
    public function getBatchActions()
    {
        return array_merge(parent::getBatchActions(), array(
                'publish' => array(
                    'label' => $this->trans('action.publish', array(), 'SonataAdminBundle'),
                    'ask_confirmation' => true
                ),
                'unpublish' => array(
                    'label' => $this->trans('action.unpublish', array(), 'SonataAdminBundle'),
                    'ask_confirmation' => true
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper->add('publication', 'trans', array(
                'label' => 'field.publication',
                'catalogue' => 'VinceCms'
            )
        );
        parent::configureListFields($mapper);
        $mapper->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array('template' => 'VinceCmsSonataAdminBundle:CRUD:list__action_delete.html.twig')
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        parent::configureDatagridFilters($mapper);
        $mapper->add('publication', 'doctrine_orm_callback', array(
                'label' => 'field.publication',
                'callback' => function () {
                    $queryBuilder = func_get_arg(0);
                    $alias = func_get_arg(1);
                    $value = func_get_arg(3);
                    if (!$value) {
                        return;
                    }
                    switch ($value['value']) {
                        case 'Never published':
                            $queryBuilder->andWhere($queryBuilder->expr()->andX(
                                $queryBuilder->expr()->isNull(sprintf('%s.startedAt', $alias)),
                                $queryBuilder->expr()->isNull(sprintf('%s.endedAt', $alias))
                            ));
                            break;

                        case 'Published':
                            $queryBuilder->andWhere($queryBuilder->expr()->andX(
                                $queryBuilder->expr()->isNull(sprintf('%s.endedAt', $alias)),
                                $queryBuilder->expr()->isNotNull(sprintf('%s.startedAt', $alias)),
                                $queryBuilder->expr()->lte(sprintf('%s.startedAt', $alias), ':now')
                            ))->setParameter('now', new \DateTime());
                            break;

                        case 'Pre-published':
                            $queryBuilder->andWhere($queryBuilder->expr()->andX(
                                $queryBuilder->expr()->isNotNull(sprintf('%s.startedAt', $alias)),
                                $queryBuilder->expr()->gt(sprintf('%s.startedAt', $alias), ':now')
                            ))->setParameter('now', new \DateTime());
                            break;

                        case 'Post-published':
                            $queryBuilder->andWhere($queryBuilder->expr()->andX(
                                $queryBuilder->expr()->isNotNull(sprintf('%s.startedAt', $alias)),
                                $queryBuilder->expr()->lt(sprintf('%s.startedAt', $alias), ':now'),
                                $queryBuilder->expr()->isNotNull(sprintf('%s.endedAt', $alias)),
                                $queryBuilder->expr()->lt(sprintf('%s.endedAt', $alias), ':now')
                            ))->setParameter('now', new \DateTime());
                            break;

                        case 'Published temp':
                            $queryBuilder->andWhere($queryBuilder->expr()->andX(
                                $queryBuilder->expr()->isNotNull(sprintf('%s.startedAt', $alias)),
                                $queryBuilder->expr()->lte(sprintf('%s.startedAt', $alias), ':now'),
                                $queryBuilder->expr()->isNotNull(sprintf('%s.endedAt', $alias)),
                                $queryBuilder->expr()->gte(sprintf('%s.endedAt', $alias), ':now')
                            ))->setParameter('now', new \DateTime());
                            break;
                    }
                }
            ), 'choice', array(
                'choices' => array(
                    'Never published' => $this->trans('Never published', array(), 'VinceCms'),
                    'Published' => $this->trans('Published', array(), 'VinceCms'),
                    'Pre-published' => $this->trans('Pre-published', array(), 'VinceCms'),
                    'Post-published' => $this->trans('Post-published', array(), 'VinceCms'),
                    'Published temp' => $this->trans('Published temp', array(), 'VinceCms')
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $mapper)
    {
        $mapper
            ->with('field.publication', array('class' => 'col-md-6'))
            ->add('startedAt', 'datepicker', array(
                    'label' => 'field.startedAt',
                    'required' => false
                )
            )
            ->add('endedAt', 'datepicker', array(
                    'label' => 'field.endedAt',
                    'required' => false
                )
            )
            ->end();
    }
}
