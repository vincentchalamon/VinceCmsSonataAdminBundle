<?php

/*
 * This file is part of the blog project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Vince\Bundle\CmsSonataAdminBundle\Controller;

use My\Bundle\CmsBundle\Entity\Article;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Manage Article translations in Admin
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class ArticleController extends PublishableController
{

    /**
     * {@inheritdoc}
     */
    public function editAction($id = null)
    {
        // the key used to lookup the template
        $templateKey = 'edit';

        $id     = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        $defaultLocale = $this->container->getParameter('vince.cms.defaultLocale');
        $locale        = $this->get('request')->get('locale');

        // Object is not in the default locale: redirect to original object with required locale
        if ($object->getLocale() != $defaultLocale) {
            if ($locale == $defaultLocale) {
                $locale = $object->getLocale();
            }
            $url = $this->admin->generateObjectUrl('edit', $object->getOriginal(), array('locale' => $locale));

            return $this->redirect($url);
        }

        // Edit or create translation
        if ($object->getLocale() != $locale) {
            if ($object->hasTranslation($locale)) {
                $object = $object->getTranslation($locale);
            } else {
                // New translation (clone original object)
                $object = clone $object;
                $object->setLocale($locale);
                //$object->getMeta('language')->setContents($locale); todo-vince Does it work ?
                $object->setOriginal($object->getOriginal());
            }
        }

        $this->admin->setSubject($object);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($object);

        if ($this->getRestMethod() == 'POST') {
            $form->submit($this->get('request'));

            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {

                try {
                    $object = $this->admin->update($object);

                    if ($this->isXmlHttpRequest()) {
                        return $this->renderJson(array(
                            'result'    => 'ok',
                            'objectId'  => $this->admin->getNormalizedIdentifier($object)
                        ));
                    }

                    $this->addFlash('sonata_flash_success', $this->admin->trans('flash_edit_success', array('%name%' => $this->admin->toString($object)), 'SonataAdminBundle'));

                    // redirect to edit mode
                    return $this->redirectTo($object);

                } catch (ModelManagerException $e) {

                    $isFormValid = false;
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash('sonata_flash_error', $this->admin->trans('flash_edit_error', array('%name%' => $this->admin->toString($object)), 'SonataAdminBundle'));
                }
            } elseif ($this->isPreviewRequested()) {
                // enable the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'edit',
            'form'   => $view,
            'object' => $object,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAction($id)
    {
        // Retrieve object
        $id     = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        // Delete related translations
        if ($this->getRestMethod() == 'DELETE') {
            foreach ($object->getTranslations() as $translation) {
                /** @var Article $translation */
                try {
                    $this->admin->delete($translation);
                } catch (ModelManagerException $e) {
                    if ($this->isXmlHttpRequest()) {
                        return $this->renderJson(array('result' => 'error'));
                    }
                    $this->addFlash('sonata_flash_error', $this->admin->trans('flash_delete_error', array(
                        '%name%' => $this->admin->toString($object)
                    ), 'SonataAdminBundle'));

                    return $this->redirectTo($object);
                }
            }
        }

        return parent::deleteAction($id);
    }
}