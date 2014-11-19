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
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Translatable admin
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class TranslatableAdmin extends Admin
{

    /**
     * Available languages
     *
     * @var array
     */
    protected $languages;

    /**
     * Set available languages
     *
     * @author Vincent Chalamon <vincent@ylly.fr>
     * @param array $languages
     */
    public function setLanguages(array $languages)
    {
        $this->languages = $languages;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('translate', $this->getRouterIdParameter().'/translate/{language}');
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
     * {@inheritdoc}
     */
    public function getNewInstance()
    {
        $object = parent::getNewInstance();
        $object->setLanguage($this->languages[0]);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterParameters()
    {
        $parameters = parent::getFilterParameters();
        if (!isset($parameters['language']['value']) || !trim($parameters['language']['value'])) {
            $parameters['language'] = array('type' => '', 'value' => $this->languages[0]);
        }

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        parent::configureDatagridFilters($mapper);
        $languages = array();
        foreach ($this->languages as $language) {
            $languages[$language] = $this->trans(sprintf('language.%s', $language), array(), 'SonataAdminBundle');
        }
        $mapper->add('language', 'doctrine_orm_choice', array(
                'label' => 'field.language'
            ), 'choice', array('choices' => $languages)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $mapper)
    {
        parent::configureListFields($mapper);
        $mapper->add('languages', 'languages', array(
                'label' => 'field.languages'
            )
        );
    }
}
