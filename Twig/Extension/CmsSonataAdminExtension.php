<?php

/*
 * This file is part of the VinceCmsSonataAdmin bundle.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Vince\Bundle\CmsSonataAdminBundle\Twig\Extension;

use Doctrine\Common\Util\Inflector;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Twig extension for CMS admin
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class CmsSonataAdminExtension extends \Twig_Extension
{

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('md5', 'md5')
        );
    }

    /**
     * MD5
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param string  $value Value
     *
     * @return string
     */
    public function md5($value)
    {
        return md5($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'vince_cms_sonata_admin';
    }
}