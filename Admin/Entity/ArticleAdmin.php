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
use Vince\Bundle\CmsBundle\Entity\ArticleMeta;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Finder\Finder;
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
class ArticleAdmin extends PublishableAdmin
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
     * Cache dir
     *
     * @var string
     */
    protected $cacheDir;

    /**
     * ArticleMeta class
     *
     * @var string
     */
    protected $articleMetaClass;

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
     * Set cache dir
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param string $cacheDir
     */
    public function setCacheDir($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * Set ArticleMeta class
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param string $articleMetaClass
     */
    public function setArticleMetaClass($articleMetaClass)
    {
        $this->articleMetaClass = $articleMetaClass;
    }

    /**
     * Check if object can be batched
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param Article $object
     *
     * @return bool
     */
    public function canBeBatched(Article $object)
    {
        return !$object->isSystem();
    }

    /**
     * Check if object can be deleted
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param Article $object
     *
     * @return bool
     */
    public function canBeDeleted(Article $object)
    {
        return $this->canBeBatched($object);
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
            $builder->expr()->in('m.name', array('language', 'robots', 'og:type', 'twitter:card', 'twitter:creator', 'twitter:author', 'author', 'publisher'))
        )->getQuery()->execute();
        foreach ($metas as $meta) {
            /** @var ArticleMeta $articleMeta */
            $articleMeta = new $this->articleMetaClass();
            /** @var Meta $meta */
            $articleMeta->setMeta($meta);
            switch ($meta->getName()) {
                case 'language':
                    $articleMeta->setContents($this->locale);
                    break;
                case 'robots':
                    $articleMeta->setContents('index,follow');
                    break;
                case 'og:type':
                    $articleMeta->setContents('article');
                    break;
                case 'twitter:card':
                    $articleMeta->setContents('summary');
                    break;
                case 'twitter:creator':
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
                    'label' => 'article.field.title',
                    'template' => 'VinceCmsSonataAdminBundle:List:url.html.twig'
                )
            )
        ;
        parent::configureListFields($mapper);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $mapper)
    {
        $mapper
            ->with('article.group.general', array('class' => 'col-md-6'))
                ->add('title', null, array(
                        'label' => 'article.field.title'
                    )
                )
                ->add('summary', 'redactor', array(
                        'label' => 'article.field.summary',
                        'help' => 'article.help.summary',
                        'minHeight' => 100
                    )
                )
                ->add('tags', 'list', array(
                        'label'    => 'article.field.tags',
                        'required' => false,
                        'help' => 'article.help.tags'
                    )
                )
            ;
        if (!$this->getSubject()->isSystem()) {
            $mapper->add('url', null, array(
                    'label' => 'article.field.customUrl',
                    'required' => false,
                    'help' => 'article.help.customUrl',
                    'attr' => array(
                        'placeholder' => $this->getSubject()->getRoutePattern()
                    )
                )
            );
        }
        $mapper->end();
        if (!$this->getSubject()->isSystem()) {
            parent::configureFormFields($mapper);
        }
        $mapper
            ->with('article.group.template', array('class' => 'col-md-12'))
                ->add('template', null, array(
                        'label' => 'article.field.template'
                    )
                )
                ->add('contents', 'template', array(
                        'label' => false
                    )
                )
            ->end()
            ->with('article.group.metas', array('class' => 'col-md-12'))
                ->add('metas', 'metagroup', array(
                        'label' => false
                    )
                )
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        /** @var Article $object */
        // Remove empty contents or from other templates
        foreach ($object->getContents() as $content) {
            /** @var Content $content */
            if ($content->getArea()->getTemplate()->getId() != $object->getTemplate()->getId()
                || !trim(strip_tags($content->getContents(), '<img><input><button><iframe>'))
                || is_null($content->getContents())) {
                $object->removeContent($content);
                $this->em->remove($content);
            }
        }
        // Remove empty metas
        foreach ($object->getMetas() as $meta) {
            /** @var ArticleMeta $meta */
            if (!trim(strip_tags($meta->getContents()))) {
                $object->removeMeta($meta);
                $this->em->remove($meta);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
        /** @var Article $object */
        // Remove empty contents or from other templates
        foreach ($object->getContents() as $content) {
            /** @var Content $content */
            if ($content->getArea()->getTemplate()->getId() != $object->getTemplate()->getId()
                || !trim(strip_tags($content->getContents(), '<img><input><button><iframe>'))
                || is_null($content->getContents())) {
                $object->removeContent($content);
                $this->em->remove($content);
            }
        }
        // Remove empty metas
        foreach ($object->getMetas() as $meta) {
            /** @var ArticleMeta $meta */
            if (!trim(strip_tags($meta->getContents()))) {
                $object->removeMeta($meta);
                $this->em->remove($meta);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist($object)
    {
        // Need to clear router cache
        $files = Finder::create()->files()->name('/app[A-z]+Url(?:Generator|Matcher)\.php/')->in($this->cacheDir);
        foreach ($files as $file) {
            /** @var \SplFileInfo $file */
            unlink($file->__toString());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate($object)
    {
        // Need to clear router cache
        $files = Finder::create()->files()->name('/app[A-z]+Url(?:Generator|Matcher)\.php/')->in($this->cacheDir);
        foreach ($files as $file) {
            /** @var \SplFileInfo $file */
            unlink($file->__toString());
        }
    }
}
