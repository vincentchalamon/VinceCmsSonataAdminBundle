<?php

/*
 * This file is part of the VinceCmsSonataAdmin bundle.
 *
 * (c) Vincent Chalamon <http://www.vincent-chalamon.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Vince\Bundle\CmsSonataAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Vince\Bundle\CmsBundle\Entity\Area;

/**
 * AreaType manage areas list for a specific template
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class AreaType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['areas'] as $area) {
            /** @var Area $area */
            $fieldOptions = array_merge(array(
                'label' => $area->getTitle(),
                'required' => $area->isRequired(),
            ), $area->getOptions());
            if ($area->getType() == 'document') {
                $fieldOptions['string'] = true;
            }
            $builder->add($area->getName(), $area->getType(), $fieldOptions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('areas'));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'area';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'form';
    }
}
