<?php

declare(strict_types=1);

/*
 * This file is part of Contao Filepond Uploader.
 *
 * (c) Marko Cupic <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-filepond-uploader
 */

namespace Markocupic\ContaoFilepondUploader\Controller\FrontendModule;

use Codefog\HasteBundle\Form\Form;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\FilesModel;
use Contao\ModuleModel;
use Markocupic\ContaoFilepondUploader\Widget\FilepondFrontendWidget;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(category: 'miscellaneous')]
class FilepondController extends AbstractFrontendModuleController
{
    public function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $form = $this->getForm();

        if ($form->isSubmitted() && $form->validate() && !$request->isXmlHttpRequest()) {
            throw new RedirectResponseException($request->getUri());
        }

        $template->set('form', $form->generate());

        return $template->getResponse();
    }

    private function getForm(): Form
    {
        $form = new Form('MyFileUploadForm', 'POST');

        // Add a sample text form field:
        $form->addFormField('filepond', [
            'inputType' => FilepondFrontendWidget::TYPE,
            'eval' => [
                'mandatory' => true,
                'uploadFolder' => $this->getContaoAdapter(FilesModel::class)->findByPath('files/filepond_test')->uuid,
                'extensions' => 'jpg,jpeg,JPEG,JPG,png,PNG',
                'storeFile' => true,
                'doNotOverwrite' => true,
                'addToDbafs' => true,
                'maxlength' => 100000000000,
                'minlength' => 100,
                'allowImageResize' => true,
                'imageResizeTargetWidth' => 2000,
                'imageResizeTargetHeight' => 2000,
                'imageResizeMode' => 'contain',
                'imageResizeUpscale' => false,
                'minImageWidth' => 100,
                'maxImageWidth' => 150000,
                'multiple' => true,
                'mSize' => 2, // max. file uploads
            ],
        ]);

        // Add a "submit" button
        $form->addSubmitFormField('Absenden');

        return $form;
    }
}
