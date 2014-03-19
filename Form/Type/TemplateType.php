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
use Vince\Bundle\CmsBundle\Entity\Template;
use Vince\Bundle\CmsSonataAdminBundle\Form\Transformer\ContentsTransformer;

/**
 * TemplateType manage contents list grouped by template name
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class TemplateType extends AbstractType
{

    /**
     * Template repository
     *
     * @var EntityRepository
     */
    protected $repository;

    /**
     * Contents class name
     *
     * @var string
     */
    protected $class;

    /**
     * Set Template repository
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param EntityRepository $repository
     *
     * @return MetaGroupType
     */
    public function setTemplateRepository(EntityRepository $repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Set Content class name
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param string $class Class name
     *
     * @return MetaGroupType
     */
    public function setContentClassName($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new ContentsTransformer($this->repository, $this->class));
        $list = $this->repository->createQueryBuilder('t')
                                 ->innerJoin('t.areas', 'a')->addSelect('a')
                                 ->getQuery()->execute();
        foreach ($list as $template) {
            /** @var Template $template */
            $builder->add($template->getSlug(), 'area', array(
                    'label' => false,
                    'areas' => $template->getAreas(),
                    'attr'  => array('template_id' => $template->getId())
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'template';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'form';
    }
}