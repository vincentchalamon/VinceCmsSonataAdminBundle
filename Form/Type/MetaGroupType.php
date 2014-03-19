<?php

/*
 * This file is part of the VinceCmsSonataAdmin bundle.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Vince\Bundle\CmsSonataAdminBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vince\Bundle\CmsBundle\Entity\Meta;
use Vince\Bundle\CmsSonataAdminBundle\Form\Transformer\MetaTransformer;

/**
 * MetaGroupType manage grouped meta list
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class MetaGroupType extends AbstractType
{

    /**
     * Meta repository
     *
     * @var EntityRepository
     */
    protected $repository;

    /**
     * ArticleMeta class name
     *
     * @var string
     */
    protected $class;

    /**
     * Set Meta repository
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param EntityRepository $repository
     *
     * @return MetaGroupType
     */
    public function setMetaRepository(EntityRepository $repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Set ArticleMeta class name
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param string $class Class name
     *
     * @return MetaGroupType
     */
    public function setArticleMetaClassName($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new MetaTransformer($this->repository, $this->class));
        $list   = $this->repository->findAll();
        $groups = array();
        foreach ($list as $meta) {
            /** @var Meta $meta */
            if (!isset($groups[$meta->getGroup()])) {
                $groups[$meta->getGroup()] = array();
            }
            $groups[$meta->getGroup()][] = $meta;
        }
        foreach ($groups as $name => $metas) {
            $builder->add($name ?: 'general', 'meta', array(
                    'label' => $name,
                    'metas' => $metas
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'metagroup';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'form';
    }
}