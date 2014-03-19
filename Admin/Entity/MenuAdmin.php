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
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Vince\Bundle\CmsBundle\Entity\Menu;
use Vince\Bundle\CmsBundle\Entity\Repository\MenuRepository;

/**
 * Menu admin
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class MenuAdmin extends Admin
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
     * Public path
     *
     * @var string
     */
    protected $publicPath;

    /**
     * Upload directory name
     *
     * @var string
     */
    protected $uploadDirName;

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
     * Set public path
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param string $publicPath
     *
     * @return MenuAdmin
     */
    public function setPublicPath($publicPath)
    {
        $this->publicPath = $publicPath;

        return $this;
    }

    /**
     * Set upload directory name
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param string $uploadDirName
     *
     * @return MenuAdmin
     */
    public function setUploadDirName($uploadDirName)
    {
        $this->uploadDirName = $uploadDirName;

        return $this;
    }

    /**
     * Need to override createQuery method because or list order & joins
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
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper->add('title');
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
        $mapper->with('menu.group.general');
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
            ->end()
            ->with('menu.group.publication')
                ->add('startedAt', 'datepicker', array(
                        'label' => 'menu.field.startedAt',
                        'required' => false
                    )
                )
                ->add('endedAt', 'datepicker', array(
                        'label' => 'menu.field.endedAt',
                        'required' => false
                    )
                )
            ->end();
        if (!$id || $this->getSubject()->getParent()) {
            $mapper
                ->with('menu.group.url')
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
        if ($object->isImage() && is_file($this->publicPath.$object->getPath())) {
            unlink($this->publicPath.$object->getPath());
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
            $object->upload($this->publicPath, $this->uploadDirName);
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
            $object->upload($this->publicPath, $this->uploadDirName);
        }
        $this->menuRepository->verify();
        $this->menuRepository->recover();
    }
}