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

use Doctrine\ORM\EntityManager;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Translatable admin
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
abstract class TranslatableAdmin extends PublishableAdmin
{

    /**
     * Available locales
     *
     * @var array
     */
    protected $locales;

    /**
     * Default locale
     *
     * @var string
     */
    protected $defaultLocale;

    /**
     * Translation repository
     *
     * @var TranslationRepository
     */
    protected $translationRepository;

    /**
     * Entity manager
     *
     * @var EntityManager
     */
    protected $em;

    /**
     * Set available locales
     *
     * @author Vincent Chalamon <vincent@ylly.fr>
     * @param array $locale
     */
    public function setLocales(array $locale)
    {
        $this->locales = $locale;
    }

    /**
     * Set default locale
     *
     * @author Vincent Chalamon <vincent@ylly.fr>
     * @param string $defaultLocale
     */
    public function setDefaultLocale($defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Set translation repository
     *
     * @author Vincent Chalamon <vincent@ylly.fr>
     * @param TranslationRepository $repository
     */
    public function setTranslationRepository(TranslationRepository $repository)
    {
        $this->translationRepository = $repository;
    }

    /**
     * Set object manager
     *
     * @author Vincent Chalamon <vincent@ylly.fr>
     * @param EntityManager $em
     */
    public function setObjectManager(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Check if Block object has translation
     *
     * @author Vincent Chalamon <vincent@ylly.fr>
     * @param string $locale
     * @param object $object
     * @return bool
     */
    public function hasTranslation($locale, $object = null)
    {
        return array_key_exists($locale, $this->translationRepository->findTranslations($object ?: $this->getSubject()));
    }

    /**
     * {@inheritdoc}
     */
    public function getObject($id)
    {
        $object = parent::getObject($id);
        $object->setLocale($this->defaultLocale);
        $this->em->refresh($object);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate($name)
    {
        if ($name == 'edit') {
            return 'VinceCmsSonataAdminBundle:CRUD:translatable_edit.html.twig';
        }

        return parent::getTemplate($name);
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
        $collection->get('edit')->setPath($this->getBaseRoutePattern().'/'.$this->getRouterIdParameter().'/edit/{locale}')
                   ->setDefault('locale', $this->defaultLocale);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper->add('locales', 'locales', array(
                'label' => 'field.locales'
            )
        );
        parent::configureListFields($mapper);
    }
}
