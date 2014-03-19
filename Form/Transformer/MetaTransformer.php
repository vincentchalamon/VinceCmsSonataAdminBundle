<?php

/*
 * This file is part of the VinceCmsSonataAdmin bundle.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Vince\Bundle\CmsSonataAdminBundle\Form\Transformer;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Vince\Bundle\CmsBundle\Entity\ArticleMeta;
use Vince\Bundle\CmsBundle\Entity\Meta;

/**
 * Meta transformer
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class MetaTransformer implements DataTransformerInterface
{

    /**
     * ArticleMeta class name
     *
     * @var string
     */
    protected $class;

    /**
     * Object manager
     *
     * @var ObjectManager
     */
    protected $em;

    /**
     * Existing values
     *
     * @var \Traversable
     */
    protected $metas;

    /**
     * @param EntityRepository $repository Meta repository
     * @param string           $class      ArticleMeta class name
     */
    public function __construct(EntityRepository $repository, $class)
    {
        $this->repository = $repository;
        $this->class      = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        // Fix for Symfony 2.4
        if (null === $value) {
            return null;
        }

        /** @var \Traversable $values */
        if (!is_array($value) && !$value instanceof \Traversable) {
            throw new \InvalidArgumentException(sprintf('Meta form type only accept array or \Traversable objects, %s sent.', is_object($value) ? 'instance of '.get_class($value) : gettype($value)));
        }

        // Keep existing values in memory for diff in reverseTransform
        $this->metas = $value;

        // Build array values for view
        $groups = array();
        foreach ($value as $articleMeta) {
            /** @var ArticleMeta $articleMeta */
            $group = $articleMeta->getMeta()->getGroup() ?: 'general';
            if (!isset($groups[$group])) {
                $groups[$group] = array();
            }
            $groups[$group][$articleMeta->getMeta()->getName()] = $articleMeta->getContents();
        }

        return $groups;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!is_array($value)) {
            return array();
        }

        $results = array();
        foreach ($value as $group => $metas) {
            foreach ($metas as $name => $contents) {
                /** @var Meta $meta */
                $meta = $this->repository->findOneBy(array('name'  => $name, 'group' => $group == 'general' ? null : $group));
                if (trim($contents) && $meta) {
                    /** @var ArticleMeta $articleMeta */
                    $articleMeta = new $this->class();
                    $articleMeta->setMeta($meta);

                    // Check existing object
                    foreach ($this->metas as $object) {
                        /** @var ArticleMeta $object */
                        if ($object->getMeta()->getId() == $meta->getId()) {
                            $articleMeta = $object;
                        }
                    }
                    $articleMeta->setContents(trim($contents));
                    $results[] = $articleMeta;
                }
            }
        }

        return $results;
    }
}