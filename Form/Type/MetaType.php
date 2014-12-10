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
use Vince\Bundle\CmsBundle\Entity\Meta;

/**
 * MetaType manage meta list for a specific group
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class MetaType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['metas'] as $meta) {
            /** @var Meta $meta */
            $builder->add($meta->getName(), $meta->getType(), array(
                    'label'    => $meta->getTitle(),
                    'required' => false
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('metas'));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'meta';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'form';
    }
}
