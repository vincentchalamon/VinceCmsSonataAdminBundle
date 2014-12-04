<?php

/*
 * This file is part of the blog project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Vince\Bundle\CmsSonataAdminBundle\Controller;

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
        $this->admin->setLocale($this->get('request')->get('locale'));

        return parent::editAction($id);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAction($id)
    {
        $this->admin->setLocale($this->get('request')->get('locale'));

        return parent::deleteAction($id);
    }
}
