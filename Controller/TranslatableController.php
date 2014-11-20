<?php

/*
 * This file is part of the blog project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Vince\Bundle\CmsSonataAdminBundle\Controller;

use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Manage translatable entities in Admin
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class TranslatableController extends PublishableController
{

    /**
     * {@inheritdoc}
     */
    public function editAction($id = null)
    {
        $this->admin->setDefaultLocale($this->get('request')->get('locale'));

        return parent::editAction($id);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAction($id)
    {
        $this->admin->setDefaultLocale($this->get('request')->get('locale'));

        return parent::deleteAction($id);
    }
}