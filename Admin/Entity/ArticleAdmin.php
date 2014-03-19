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

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use My\Bundle\CmsBundle\Entity\ArticleMeta;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Security\Core\SecurityContext;
use Vince\Bundle\CmsBundle\Entity\Article;
use Vince\Bundle\CmsBundle\Entity\Content;
use Vince\Bundle\CmsBundle\Entity\Meta;
use Vince\Bundle\TypeBundle\Listener\LocaleListener;
use Sonata\UserBundle\Entity\BaseUser;

/**
 * Article admin
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class ArticleAdmin extends Admin
{

    /**
     * {@inheritdoc}
     */
    protected $baseRoutePattern = 'articles';

    /**
     * {@inheritdoc}
     */
    protected $datagridValues = array(
        '_page'       => 1,
        '_sort_order' => 'ASC',
        '_sort_by'    => 'title'
    );

    /**
     * Meta repository
     *
     * @var EntityRepository
     */
    protected $repository;

    /**
     * Locale
     *
     * @var string
     */
    protected $locale;

    /**
     * User
     *
     * @var BaseUser
     */
    protected $user;

    /**
     * Object manager
     *
     * @var ObjectManager
     */
    protected $em;

    /**
     * Set Meta repository
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param EntityRepository $repository
     */
    public function setMetaRepository(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Set ObjectManager
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param ObjectManager $em
     */
    public function setObjectManager(ObjectManager $em)
    {
        $this->em = $em;
    }

    /**
     * Set locale
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param LocaleListener $listener
     */
    public function setLocale(LocaleListener $listener)
    {
        $this->locale = $listener->getLocale();
    }

    /**
     * Set user
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param SecurityContext $context
     */
    public function setUser(SecurityContext $context)
    {
        $this->user = $context->getToken() ? $context->getToken()->getUser() : null;
    }

    /**
     * Need to override createQuery method because or list order & joins
     *
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $query->leftJoin($query->getRootAlias().'.template', 'template')->addSelect('template')
              ->leftJoin('template.areas', 'area')->addSelect('area')
              ->leftJoin($query->getRootAlias().'.metas', 'articleMeta')->addSelect('articleMeta')
              ->leftJoin('articleMeta.meta', 'meta')->addSelect('meta');

        return $query;
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
    public function getNewInstance()
    {
        /** @var Article $article */
        $article = parent::getNewInstance();
        $builder = $this->repository->createQueryBuilder('m');
        $metas   = $builder->where(
            $builder->expr()->in('m.name', array('language', 'og:type', 'twitter:card', 'author', 'publisher', 'twitter:creator'))
        )->getQuery()->execute();
        foreach ($metas as $meta) {
            /** @var Meta $meta */
            $articleMeta = new ArticleMeta();
            $articleMeta->setMeta($meta);
            switch ($meta->getName()) {
                case 'language':
                    $articleMeta->setContents($this->locale);
                    break;
                case 'og:type':
                    $articleMeta->setContents('article');
                    break;
                case 'twitter:card':
                    $articleMeta->setContents('summary');
                    break;
                case 'twitter:author':
                    if ($this->user && $this->user->getTwitterName()) {
                        $articleMeta->setContents('@'.$this->user->getTwitterName());
                    }
                    break;
                case 'author':
                    if ($this->user) {
                        $articleMeta->setContents(trim($this->user->getFirstname().' '.$this->user->getLastname()) ?: $this->user->getUsername());
                    }
                    break;
                case 'publisher':
                    if ($this->user && $this->user->getGplusName()) {
                        $articleMeta->setContents($this->user->getGplusName());
                    }
                    break;
            }
            if ($articleMeta->getContents()) {
                $article->addMeta($articleMeta);
            }
        }

        return $article;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
            ->addIdentifier('title', null, array(
                    'label' => 'article.field.title'
                )
            )
            ->add('url', 'url', array(
                    'label' => 'article.field.url'
                )
            )
            ->add('publication', 'trans', array(
                    'label' => 'article.field.publication',
                    'catalogue' => 'VinceCms'
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
                    'label' => 'article.field.title'
                )
            )
            ->add('publication', 'doctrine_orm_callback', array(
                    'label' => 'article.field.publication',
                    'callback' => function ($queryBuilder, $alias, $field, $value) {
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
            ->with('article.group.general')
                ->add('title', null, array(
                        'label' => 'article.field.title'
                    )
                )
                ->add('summary', null, array(
                        'label' => 'article.field.summary',
                        'required' => false,
                        'help' => 'article.help.summary'
                    )
                )
                ->add('tags', 'list', array(
                        'label'    => 'article.field.tags',
                        'required' => false,
                        'help' => 'article.help.tags'
                    )
                )
            ;
        if ($this->getSubject()->getSlug() != 'homepage') {
            $mapper
                    ->add('url', null, array(
                            'label' => 'article.field.customUrl',
                            'required' => false,
                            'help' => 'article.help.customUrl',
                            'attr' => array(
                                'placeholder' => $this->getSubject()->getRoutePattern()
                            )
                        )
                    )
                ->end()
                ->with('article.group.publication')
                    ->add('startedAt', 'datepicker', array(
                            'label' => 'article.field.startedAt',
                            'required' => false
                        )
                    )
                    ->add('endedAt', 'datepicker', array(
                            'label' => 'article.field.endedAt',
                            'required' => false
                        )
                    )
            ;
        }
        $mapper
            ->end()
            ->with('article.group.metas')
                ->add('metas', 'metagroup', array(
                        'label' => false
                    )
                )
            ->end()
            ->with('article.group.template')
                ->add('template', null, array(
                        'label' => 'article.field.template',
                        'required' => false
                    )
                )
                ->add('contents', 'template', array(
                        'label' => false
                    )
                )
            ->end()
        ;
    }

    /**
     * Need to remove contents for other templates.
     * Force relation because of doctrine2 bug on cascade persist for OneToMany association.
     *
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        /** @var Article $object */
        foreach ($object->getMetas() as $meta) {
            /** @var ArticleMeta $meta */
            $meta->setArticle($object);
        }
        foreach ($object->getContents() as $content) {
            /** @var Content $content */
            $content->setArticle($object);
            if ($content->getArea()->getTemplate()->getId() != $object->getTemplate()->getId()) {
                $object->removeContent($content);
                $this->em->remove($content);
            }
        }
    }

    /**
     * Need to remove contents for other templates.
     * Force relation because of doctrine2 bug on cascade persist for OneToMany association.
     *
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
        /** @var Article $object */
        foreach ($object->getMetas() as $meta) {
            /** @var ArticleMeta $meta */
            $meta->setArticle($object);
        }
        foreach ($object->getContents() as $content) {
            /** @var Content $content */
            $content->setArticle($object);
            if ($content->getArea()->getTemplate()->getId() != $object->getTemplate()->getId()) {
                $object->removeContent($content);
                $this->em->remove($content);
            }
        }
    }
}