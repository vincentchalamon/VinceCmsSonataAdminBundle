<?php

/*
 * This file is part of the blog project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Vince\Bundle\CmsSonataAdminBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class PublishableController
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class PublishableController extends CRUDController
{

    /**
     * Execute a batch publish
     *
     * @param ProxyQueryInterface $query
     *
     * @return RedirectResponse
     */
    public function batchActionPublish(ProxyQueryInterface $query)
    {
        try {
            $objects = $query->select('DISTINCT '.$query->getRootAlias())->getQuery()->iterate();
            $i       = 0;
            $em      = $this->get('doctrine.orm.default_entity_manager');
            foreach ($objects as $object) {
                $object[0]->publish();
                $em->persist($object[0]);
                if ((++$i % 20) == 0) {
                    $em->flush();
                    $em->clear();
                }
            }
            $em->flush();
            $em->clear();
            $this->addFlash('sonata_flash_success', 'flash.success.batch_publish');
        } catch (ModelManagerException $e) {
            $this->addFlash('sonata_flash_error', 'flash.error.batch_publish');
        }

        return new RedirectResponse($this->admin->generateUrl('list', array('filter' => $this->admin->getFilterParameters())));
    }

    /**
     * Execute a batch unpublish
     *
     * @param ProxyQueryInterface $query
     *
     * @return RedirectResponse
     */
    public function batchActionUnpublish(ProxyQueryInterface $query)
    {
        try {
            $objects = $query->select('DISTINCT '.$query->getRootAlias())->getQuery()->iterate();
            $i       = 0;
            $em      = $this->get('doctrine.orm.default_entity_manager');
            foreach ($objects as $object) {
                if ($object[0]->getSlug() != 'homepage') {
                    $object[0]->unpublish();
                }
                $em->persist($object[0]);
                if ((++$i % 20) == 0) {
                    $em->flush();
                    $em->clear();
                }
            }
            $em->flush();
            $em->clear();
            $this->addFlash('sonata_flash_success', 'flash.success.batch_unpublish');
        } catch (ModelManagerException $e) {
            $this->addFlash('sonata_flash_error', 'flash.error.batch_unpublish');
        }

        return new RedirectResponse($this->admin->generateUrl('list', array('filter' => $this->admin->getFilterParameters())));
    }
}