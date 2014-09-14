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

use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Vince\Bundle\CmsBundle\Entity\Menu;
use Vince\Bundle\CmsBundle\Entity\Repository\MenuRepository;

/**
 * Menu admin
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class MenuAdmin extends PublishableAdmin
{

    /**
     * {@inheritdoc}
     */
    protected $baseRoutePattern = 'menus';

    /**
     * Menu repository
     *
     * @var MenuRepository
     */
    protected $menuRepository;

    /**
     * Upload directory
     *
     * @var string
     */
    protected $uploadDir;

    /**
     * Web directory
     *
     * @var string
     */
    protected $webDir;

    /**
     * Check if object can be batched
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param Menu $object
     *
     * @return bool
     */
    public function canBatch(Menu $object)
    {
        return !$object->isRoot();
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);
        $collection->add('up', $this->getRouterIdParameter().'/up')
                   ->add('down', $this->getRouterIdParameter().'/down');
    }

    /**
     * Set Menu repository
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param MenuRepository $menuRepository
     *
     * @return MenuAdmin
     */
    public function setMenuRepository(MenuRepository $menuRepository)
    {
        $this->menuRepository = $menuRepository;

        return $this;
    }

    /**
     * Set upload directory
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param string $uploadDir
     * @param string $webDir
     *
     * @return MenuAdmin
     */
    public function setUploadDir($uploadDir, $webDir)
    {
        $this->uploadDir = $uploadDir;
        $this->webDir    = $webDir;

        return $this;
    }

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
     * Need to override createQuery method because of list order & joins
     *
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $query->leftJoin($query->getRootAlias().'.article', 'article')->addSelect('article')
              ->leftJoin($query->getRootAlias().'.parent', 'parent')->addSelect('parent')
              ->leftJoin($query->getRootAlias().'.children', 'children')->addSelect('children')
              ->orderBy($query->getRootAlias().'.root, '.$query->getRootAlias().'.lft', 'ASC');

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
            ->add('up', 'field_tree_up', array(
                    'label'=> 'menu.field.up'
                )
            )
            ->add('down', 'field_tree_down', array(
                    'label'=> 'menu.field.down'
                )
            )
            ->addIdentifier('adminListTitle', 'html', array(
                    'label' => 'menu.field.title'
                )
            )
            ->add('route', 'url', array(
                    'label' => 'menu.field.url'
                )
            )
        ;
        parent::configureListFields($mapper);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormTheme()
    {
        return array_merge(parent::getFormTheme(), array('VinceCmsSonataAdminBundle:Form:form_theme.html.twig'));
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $mapper)
    {
        $id = $this->getSubject()->getId();
        $mapper->with('menu.group.general', array('class' => 'col-md-6'));
        if (!$id || $this->getSubject()->getLvl()) {
            $mapper
                ->add('parent', null, array(
                        'label' => 'menu.field.parent',
                        'property' => 'adminListTitle',
                        'query_builder' => function (EntityRepository $entityRepository) use ($id) {
                            $builder = $entityRepository->createQueryBuilder('p')->orderBy('p.root, p.lft', 'ASC');
                            if ($id) {
                                $builder->andWhere('p.id != :id')->setParameter('id', $id);
                            }

                            return $builder;
                        }
                    )
                );
        }
        $mapper->add('title', null, array(
                        'label' => 'menu.field.title'
                    )
                )
                ->add('image', null, array(
                        'label' => 'menu.field.image',
                        'required' => false
                    )
                )
                ->add('file', 'file', array(
                        'label' => 'menu.field.path',
                        'required' => false,
                        'filename' => $this->getSubject()->getPath()
                    )
                )
            ->end();
        parent::configureFormFields($mapper);
        if (!$id || $this->getSubject()->getParent()) {
            $mapper
                ->with('menu.group.url', array('class' => 'col-md-6'))
                    ->add('url', null, array(
                            'label' => 'menu.field.url',
                            'help' => 'menu.help.url',
                            'required' => false
                        )
                    )
                    ->add('article', null, array(
                            'label' => 'menu.field.article',
                            'help' => 'menu.help.article',
                            'required' => false
                        )
                    )
                    ->add('target', 'choice', array(
                            'label' => 'menu.field.target',
                            'required' => false,
                            'choices' => array(
                                '_blank' => $this->trans('menu.help.target.blank', array(), 'SonataAdminBundle'),
                                '_self'  => $this->trans('menu.help.target.self', array(), 'SonataAdminBundle')
                            )
                        )
                    )
                ->end();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function preRemove($object)
    {
        /** @var Menu $object */
        if ($object->isImage() && is_file($this->uploadDir.pathinfo($object->getPath(), PATHINFO_BASENAME))) {
            unlink($this->uploadDir.pathinfo($object->getPath(), PATHINFO_BASENAME));
        }
        $this->menuRepository->verify();
        $this->menuRepository->recover();
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        /** @var Menu $object */
        if ($object->isImage()) {
            $object->upload($this->uploadDir, $this->webDir);
        }
        $this->menuRepository->verify();
        $this->menuRepository->recover();
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
        /** @var Menu $object */
        if ($object->isImage()) {
            $object->upload($this->uploadDir, $this->webDir);
        }
        $this->menuRepository->verify();
        $this->menuRepository->recover();
    }
}
