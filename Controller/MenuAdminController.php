<?php

/*
 * This file is part of the VinceCmsSonataAdmin bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Vince\Bundle\CmsSonataAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Vince\Bundle\CmsBundle\Entity\Menu;
use Vince\Bundle\CmsBundle\Entity\Repository\MenuRepository;

/**
 * Manage Menu position in Admin
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class MenuAdminController extends Controller
{

    /**
     * Move menu up
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function upAction(Request $request)
    {
        /** @var MenuRepository $repo */
        $repo = $this->get('vince.repository.menu');
        /** @var Menu $menu */
        $menu = $repo->find($request->get('id'));
        if ($menu->getParent()){
            $repo->moveUp($menu);
        }

        return $this->redirect($this->generateUrl('admin_my_cms_menu_list'));
    }

    /**
     * Move menu down
     *
     * @author Vincent Chalamon <vincentchalamon@gmail.com>
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function downAction(Request $request)
    {
        /** @var MenuRepository $repo */
        $repo = $this->get('vince.repository.menu');
        /** @var Menu $menu */
        $menu = $repo->find($request->get('id'));
        if ($menu->getParent()){
            $repo->moveDown($menu);
        }

        return $this->redirect($this->generateUrl('admin_my_cms_menu_list'));
    }
}